<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Employees\Models\Employee;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Radiography\Services\RadiographyService;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Models\DecoderCleanup;
use App\Modules\Shifts\Models\DecoderDecryption;
use App\Modules\Shifts\Models\DecoderFilmGroup;
use App\Modules\Shifts\Models\DecoderForgerySuspicion;
use App\Modules\Shifts\Models\DecoderReject;
use App\Modules\Shifts\Models\DecoderShiftReport;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DecoderShiftService
{
    use RecordsAuditLogs;

    public function start(Employee $employee, ?string $comment = null, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Shift
    {
        return DB::transaction(function () use ($employee, $comment, $actor, $ipAddress, $userAgent): Shift {
            $this->ensureNoOpenShift($employee, ShiftType::Decoder);

            $shift = Shift::query()->create([
                'employee_id' => $employee->getKey(),
                'object_id' => $employee->object_id,
                'type' => ShiftType::Decoder,
                'status' => ShiftStatus::Open,
                'started_at' => now(),
                'comment' => $comment,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Shift::class,
                    entityId: $shift->getKey(),
                    operation: 'shift.decoder.started',
                    after: $this->snapshot($shift->refresh()),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $shift;
        });
    }

    /**
     * @param  array{summary?: string|null, comment?: string|null, completed_at?: string|null}  $data
     */
    public function addReport(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DecoderShiftReport
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): DecoderShiftReport {
            $report = $shift->decoderReport()->updateOrCreate(
                ['shift_id' => $shift->getKey()],
                [
                    'summary' => $data['summary'] ?? null,
                    'comment' => $data['comment'] ?? null,
                    'completed_at' => $data['completed_at'] ?? null,
                ],
            );

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DecoderShiftReport::class,
                    entityId: $report->getKey(),
                    operation: 'decoder_shift_report.saved',
                    after: [
                        'id' => $report->getKey(),
                        'shift_id' => $report->shift_id,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $report;
        });
    }

    /**
     * @param  array{rt_result_id?: int|null, group_name: string, viewed_at?: string|null, comment?: string|null}  $data
     */
    public function addFilmGroup(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DecoderFilmGroup
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): DecoderFilmGroup {
            $group = $shift->decoderFilmGroups()->create([
                'rt_result_id' => $data['rt_result_id'] ?? null,
                'group_name' => $data['group_name'],
                'viewed_at' => $data['viewed_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->markInProgress($shift);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DecoderFilmGroup::class,
                    entityId: $group->getKey(),
                    operation: 'decoder_film_group.created',
                    after: [
                        'id' => $group->getKey(),
                        'shift_id' => $group->shift_id,
                        'group_name' => $group->group_name,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $group;
        });
    }

    /**
     * @param  array{rt_result_id?: int|null, reason: string, recorded_at?: string|null, comment?: string|null}  $data
     */
    public function addReject(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DecoderReject
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): DecoderReject {
            $reject = $shift->decoderRejects()->create([
                'rt_result_id' => $data['rt_result_id'] ?? null,
                'reason' => $data['reason'],
                'recorded_at' => $data['recorded_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DecoderReject::class,
                    entityId: $reject->getKey(),
                    operation: 'decoder_reject.created',
                    after: [
                        'id' => $reject->getKey(),
                        'shift_id' => $reject->shift_id,
                        'reason' => $reject->reason,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $reject;
        });
    }

    /**
     * @param  array{rt_result_id?: int|null, reason: string, recorded_at?: string|null, comment?: string|null}  $data
     */
    public function addForgerySuspicion(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DecoderForgerySuspicion
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): DecoderForgerySuspicion {
            $suspicion = $shift->decoderForgerySuspicion()->create([
                'rt_result_id' => $data['rt_result_id'] ?? null,
                'reason' => $data['reason'],
                'recorded_at' => $data['recorded_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DecoderForgerySuspicion::class,
                    entityId: $suspicion->getKey(),
                    operation: 'decoder_forgery_suspicion.created',
                    after: [
                        'id' => $suspicion->getKey(),
                        'shift_id' => $suspicion->shift_id,
                        'reason' => $suspicion->reason,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $suspicion;
        });
    }

    /**
     * @param  array{completed_at?: string|null, comment?: string|null}  $data
     */
    public function addCleanup(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DecoderCleanup
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): DecoderCleanup {
            $cleanup = $shift->decoderCleanups()->create([
                'completed_at' => $data['completed_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DecoderCleanup::class,
                    entityId: $cleanup->getKey(),
                    operation: 'decoder_cleanup.created',
                    after: [
                        'id' => $cleanup->getKey(),
                        'shift_id' => $cleanup->shift_id,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $cleanup;
        });
    }

    /**
     * @param  array{rt_result_id?: int|null, result_text?: string|null, analysis_comment?: string|null, decrypted_at?: string|null}  $data
     */
    public function addDecryption(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): DecoderDecryption
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): DecoderDecryption {
            $decryption = $shift->decoderDecryptions()->create([
                'rt_result_id' => $data['rt_result_id'] ?? null,
                'result_text' => $data['result_text'] ?? null,
                'analysis_comment' => $data['analysis_comment'] ?? null,
                'decrypted_at' => $data['decrypted_at'] ?? now(),
            ]);

            if ($decryption->rt_result_id !== null) {
                $rtResult = RtResult::query()->findOrFail($decryption->rt_result_id);
                app(RadiographyService::class)->markDecoded($rtResult, $actor, 'Дешифровка завершена', $ipAddress, $userAgent);
            }

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: DecoderDecryption::class,
                    entityId: $decryption->getKey(),
                    operation: 'decoder_decryption.created',
                    after: [
                        'id' => $decryption->getKey(),
                        'shift_id' => $decryption->shift_id,
                        'rt_result_id' => $decryption->rt_result_id,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $decryption;
        });
    }

    public function complete(Shift $shift, ?string $comment = null, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Shift
    {
        return DB::transaction(function () use ($shift, $comment, $actor, $ipAddress, $userAgent): Shift {
            if ($shift->decoderReport === null) {
                throw ValidationException::withMessages([
                    'report' => 'Для завершения смены нужен сменный отчет.',
                ]);
            }

            if ($shift->decoderCleanups()->count() === 0) {
                throw ValidationException::withMessages([
                    'completed_at' => 'Для завершения смены нужна отметка об очистке рабочего места.',
                ]);
            }

            if ($shift->decoderDecryptions()->count() === 0) {
                throw ValidationException::withMessages([
                    'decrypted_at' => 'Для завершения смены нужен хотя бы один результат дешифровки.',
                ]);
            }

            $before = $this->snapshot($shift);
            $shift->fill([
                'status' => ShiftStatus::Completed,
                'finished_at' => now(),
                'comment' => $comment ?? $shift->comment,
            ])->save();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Shift::class,
                    entityId: $shift->getKey(),
                    operation: 'shift.decoder.completed',
                    before: $before,
                    after: $this->snapshot($shift->refresh()),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $shift;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Shift $shift): array
    {
        return [
            'id' => $shift->getKey(),
            'employee_id' => $shift->employee_id,
            'object_id' => $shift->object_id,
            'type' => $shift->type->value,
            'status' => $shift->status->value,
        ];
    }

    private function ensureNoOpenShift(Employee $employee, ShiftType $type): void
    {
        $existing = Shift::query()
            ->where('employee_id', $employee->getKey())
            ->where('type', $type)
            ->whereIn('status', [ShiftStatus::Open->value, ShiftStatus::InProgress->value, ShiftStatus::AwaitingCompletion->value])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'employee_id' => 'Для этого сотрудника уже есть открытая смена данного типа.',
            ]);
        }
    }

    private function markInProgress(Shift $shift): void
    {
        if ($shift->status === ShiftStatus::Completed || $shift->status === ShiftStatus::Cancelled) {
            return;
        }

        $shift->fill(['status' => ShiftStatus::InProgress])->save();
    }
}
