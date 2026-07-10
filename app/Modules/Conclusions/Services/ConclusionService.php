<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Conclusions\Enums\ConclusionStatus;
use App\Modules\Conclusions\Jobs\GenerateConclusionPdfJob;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Conclusions\Models\ConclusionItem;
use App\Modules\Documents\Models\File;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ConclusionService
{
    use RecordsAuditLogs;

    public function __construct(
        private readonly ConclusionVersionService $versionService,
    ) {
    }

    /**
     * @param  array{
     *     number: string,
     *     date: string,
     *     result_ids: array<int, int|string>,
     *     comment?: string|null
     * }  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): Conclusion {
            $results = $this->loadReadyResults($data['result_ids']);
            $this->assertResultsCompatible($results);
            $firstResult = $results->first();
            $objectId = $firstResult?->weld?->object_id;
            $methodId = $firstResult?->ndt_method_id;
            $requestId = $firstResult?->task?->ndt_request_id;

            $conclusion = Conclusion::query()->create([
                'number' => $data['number'],
                'date' => $data['date'],
                'object_id' => $objectId,
                'ndt_method_id' => $methodId,
                'ndt_request_id' => $requestId,
                'prepared_by_employee_id' => $this->employeeId($actor),
                'checked_by_employee_id' => null,
                'approved_by_employee_id' => null,
                'status' => ConclusionStatus::Prepared->value,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->syncItems($conclusion, $results);
            $this->recordStatusHistory($conclusion, null, ConclusionStatus::Prepared, $actor, $data['comment'] ?? null);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: 'conclusion.created',
                    after: $this->snapshot($conclusion->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $conclusion->refresh();
        });
    }

    /**
     * @param  array{
     *     number: string,
     *     date: string,
     *     result_ids?: array<int, int|string>|null,
     *     comment?: string|null
     * }  $data
     */
    public function update(Conclusion $conclusion, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        $this->ensureEditable($conclusion);

        return DB::transaction(function () use ($conclusion, $data, $actor, $ipAddress, $userAgent): Conclusion {
            $before = $this->snapshot($conclusion);
            $conclusion->fill([
                'number' => $data['number'],
                'date' => $data['date'],
                'comment' => $data['comment'] ?? null,
            ])->save();

            if (array_key_exists('result_ids', $data) && is_array($data['result_ids'])) {
                $results = $this->loadReadyResults($data['result_ids']);
                $this->assertResultsCompatible($results, $conclusion);
                $this->syncItems($conclusion, $results);
            }

            $conclusion->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: 'conclusion.updated',
                    before: $before,
                    after: $this->snapshot($conclusion),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $conclusion;
        });
    }

    public function submitForApproval(Conclusion $conclusion, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        if (! $conclusion->status->canBeSubmitted()) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя отправить заключение на проверку из текущего статуса.',
            ]);
        }

        return $this->transition($conclusion, ConclusionStatus::OnCheck, 'conclusion.sent_to_check', $actor, $comment, $ipAddress, $userAgent);
    }

    public function approve(Conclusion $conclusion, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        if (! $conclusion->status->canBeApproved()) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя утвердить заключение из текущего статуса.',
            ]);
        }

        return DB::transaction(function () use ($conclusion, $actor, $comment, $ipAddress, $userAgent): Conclusion {
            $before = $this->snapshot($conclusion);
            $fromStatus = $conclusion->status;
            $conclusion->forceFill([
                'checked_by_employee_id' => $this->employeeId($actor) ?? $conclusion->checked_by_employee_id,
                'approved_by_employee_id' => $this->employeeId($actor) ?? $conclusion->approved_by_employee_id,
                'status' => ConclusionStatus::Approved->value,
            ])->save();
            $conclusion->refresh();

            $this->recordStatusHistory($conclusion, $fromStatus, ConclusionStatus::Approved, $actor, $comment);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: 'conclusion.approved',
                    before: $before,
                    after: $this->snapshot($conclusion),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            app(NotificationService::class)->notifyConclusionWaitingApproval($conclusion);

            return $conclusion;
        });
    }

    public function returnForRevision(Conclusion $conclusion, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        if (! in_array($conclusion->status, [ConclusionStatus::OnCheck, ConclusionStatus::Approved], true)) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя вернуть заключение на доработку из текущего статуса.',
            ]);
        }

        return DB::transaction(function () use ($conclusion, $actor, $comment, $ipAddress, $userAgent): Conclusion {
            $before = $this->snapshot($conclusion);
            $fromStatus = $conclusion->status;
            $conclusion->forceFill([
                'checked_by_employee_id' => $this->employeeId($actor) ?? $conclusion->checked_by_employee_id,
                'status' => ConclusionStatus::Returned->value,
            ])->save();
            $conclusion->refresh();

            $this->recordStatusHistory($conclusion, $fromStatus, ConclusionStatus::Returned, $actor, $comment);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: 'conclusion.returned',
                    before: $before,
                    after: $this->snapshot($conclusion),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            app(NotificationService::class)->notifyConclusionReturned($conclusion);

            return $conclusion;
        });
    }

    public function issue(Conclusion $conclusion, string $basis, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        if (! $conclusion->status->canBeIssued()) {
            throw ValidationException::withMessages([
                'status' => 'Выдача возможна только после утверждения заключения.',
            ]);
        }

        GenerateConclusionPdfJob::dispatch(
            conclusionId: $conclusion->getKey(),
            basis: $basis,
            actorId: $actor?->getKey(),
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return $this->transition($conclusion, ConclusionStatus::Issued, 'conclusion.issued', $actor, $basis, $ipAddress, $userAgent);
    }

    public function annul(Conclusion $conclusion, string $reason, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        if (in_array($conclusion->status, [ConclusionStatus::Annulled, ConclusionStatus::Replaced], true)) {
            return $conclusion;
        }

        return DB::transaction(function () use ($conclusion, $reason, $actor, $comment, $ipAddress, $userAgent): Conclusion {
            $before = $this->snapshot($conclusion);
            $fromStatus = $conclusion->status;
            $this->versionService->cancelCurrentVersions($conclusion, $actor, $ipAddress, $userAgent);
            $conclusion->forceFill([
                'status' => ConclusionStatus::Annulled->value,
            ])->save();
            $conclusion->refresh();

            $this->recordStatusHistory($conclusion, $fromStatus, ConclusionStatus::Annulled, $actor, $comment ?? $reason);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: 'conclusion.annulled',
                    before: $before,
                    after: $this->snapshot($conclusion),
                    actor: $actor,
                    reason: $reason,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $conclusion;
        });
    }

    /**
     * Create a replacement draft from an existing conclusion.
     *
     * @param  array{
     *     number: string,
     *     date: string,
     *     reason: string,
     *     comment?: string|null
     * }  $data
     */
    public function replace(Conclusion $conclusion, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        if (in_array($conclusion->status, [ConclusionStatus::Annulled, ConclusionStatus::Replaced], true)) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя заменить уже аннулированное или замененное заключение.',
            ]);
        }

        return DB::transaction(function () use ($conclusion, $data, $actor, $ipAddress, $userAgent): Conclusion {
            $fromStatus = $conclusion->status;
            $results = $conclusion->items()->with('result')->get()->map(static fn (ConclusionItem $item): ?NdtResult => $item->result)->filter();
            $this->versionService->supersedeCurrentVersions($conclusion, $actor, $ipAddress, $userAgent);

            $before = $this->snapshot($conclusion);
            $conclusion->forceFill([
                'status' => ConclusionStatus::Replaced->value,
            ])->save();
            $conclusion->refresh();

            $this->recordStatusHistory($conclusion, $fromStatus, ConclusionStatus::Replaced, $actor, $data['reason']);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: 'conclusion.replaced',
                    before: $before,
                    after: $this->snapshot($conclusion),
                    actor: $actor,
                    reason: $data['reason'],
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            $replacement = Conclusion::query()->create([
                'number' => $data['number'],
                'date' => $data['date'],
                'object_id' => $conclusion->object_id,
                'ndt_method_id' => $conclusion->ndt_method_id,
                'ndt_request_id' => $conclusion->ndt_request_id,
                'prepared_by_employee_id' => $this->employeeId($actor),
                'checked_by_employee_id' => null,
                'approved_by_employee_id' => null,
                'status' => ConclusionStatus::Draft->value,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->syncItems($replacement, $results);
            $this->recordStatusHistory($replacement, null, ConclusionStatus::Draft, $actor, $data['reason']);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $replacement->getKey(),
                    operation: 'conclusion.replacement.created',
                    after: $this->snapshot($replacement->refresh()),
                    actor: $actor,
                    reason: $data['reason'],
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $replacement->refresh();
        });
    }

    public function attachFile(Conclusion $conclusion, File $file, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        DB::transaction(function () use ($conclusion, $file, $actor, $ipAddress, $userAgent): void {
            $conclusion->files()->syncWithoutDetaching([
                $file->getKey() => ['attached_by_user_id' => $actor?->getKey()],
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: 'conclusion.file.attached',
                    after: [
                        'conclusion_id' => $conclusion->getKey(),
                        'file_id' => $file->getKey(),
                        'attached_by_user_id' => $actor?->getKey(),
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Conclusion $conclusion): array
    {
        return [
            'id' => $conclusion->getKey(),
            'number' => $conclusion->number,
            'date' => $conclusion->date?->toDateString(),
            'object_id' => $conclusion->object_id,
            'ndt_method_id' => $conclusion->ndt_method_id,
            'ndt_request_id' => $conclusion->ndt_request_id,
            'prepared_by_employee_id' => $conclusion->prepared_by_employee_id,
            'checked_by_employee_id' => $conclusion->checked_by_employee_id,
            'approved_by_employee_id' => $conclusion->approved_by_employee_id,
            'status' => $conclusion->status->value,
            'comment' => $conclusion->comment,
        ];
    }

    private function transition(Conclusion $conclusion, ConclusionStatus $toStatus, string $operation, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): Conclusion
    {
        return DB::transaction(function () use ($conclusion, $toStatus, $operation, $actor, $comment, $ipAddress, $userAgent): Conclusion {
            $before = $this->snapshot($conclusion);
            $fromStatus = $conclusion->status;
            $conclusion->forceFill([
                'status' => $toStatus->value,
            ])->save();
            $conclusion->refresh();

            $this->recordStatusHistory($conclusion, $fromStatus, $toStatus, $actor, $comment);
            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Conclusion::class,
                    entityId: $conclusion->getKey(),
                    operation: $operation,
                    before: $before,
                    after: $this->snapshot($conclusion),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $conclusion;
        });
    }

    /**
     * @param  array<int, int|string>  $resultIds
     */
    private function loadReadyResults(array $resultIds): Collection
    {
        $ids = array_values(array_map(static fn (int|string $id): int => (int) $id, $resultIds));

        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages([
                'result_ids' => 'Результаты не должны повторяться.',
            ]);
        }

        $results = NdtResult::query()
            ->with(['weld', 'task'])
            ->whereIn('id', $ids)
            ->get();

        if ($results->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'result_ids' => 'Не все результаты найдены.',
            ]);
        }

        foreach ($results as $result) {
            if ($result->status !== NdtResultStatus::ReadyForConclusion) {
                throw ValidationException::withMessages([
                    'result_ids' => 'В заключение можно включать только результаты, готовые к заключению.',
                ]);
            }
        }

        return $results;
    }

    private function assertResultsCompatible(Collection $results, ?Conclusion $currentConclusion = null): void
    {
        $first = $results->first();

        if (! $first instanceof NdtResult) {
            throw ValidationException::withMessages([
                'result_ids' => 'Нужен хотя бы один результат.',
            ]);
        }

        $objectId = $first->weld?->object_id;
        $methodId = $first->ndt_method_id;
        $requestId = $first->task?->ndt_request_id;

        foreach ($results as $result) {
            if ($result->weld?->object_id !== $objectId || $result->ndt_method_id !== $methodId || $result->task?->ndt_request_id !== $requestId) {
                throw ValidationException::withMessages([
                    'result_ids' => 'Результаты должны относиться к одному объекту, методу и заявке.',
                ]);
            }
        }

        if ($currentConclusion !== null && ($currentConclusion->object_id !== $objectId || $currentConclusion->ndt_method_id !== $methodId)) {
            throw ValidationException::withMessages([
                'result_ids' => 'Набор результатов не соответствует текущему заключению.',
            ]);
        }
    }

    private function syncItems(Conclusion $conclusion, Collection $results): void
    {
        $conclusion->items()->delete();

        foreach ($results->values() as $index => $result) {
            $conclusion->items()->create([
                'ndt_result_id' => $result->getKey(),
                'sort_order' => $index + 1,
                'comment' => null,
            ]);
        }
    }

    private function recordStatusHistory(Conclusion $conclusion, ?ConclusionStatus $from, ConclusionStatus $to, ?User $actor = null, ?string $comment = null): void
    {
        $conclusion->statusHistory()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by_user_id' => $actor?->getKey(),
            'comment' => $comment,
        ]);
    }

    private function ensureEditable(Conclusion $conclusion): void
    {
        if (! $conclusion->status->canBeEdited()) {
            throw ValidationException::withMessages([
                'status' => 'Утвержденное, выданное, аннулированное и замененное заключение нельзя редактировать напрямую.',
            ]);
        }
    }

    private function employeeId(?User $actor): ?int
    {
        return $actor?->primaryEmployee()?->getKey();
    }
}
