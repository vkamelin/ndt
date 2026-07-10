<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Employees\Models\Employee;
use App\Modules\Inventory\Models\ChemicalInventoryTransaction;
use App\Modules\Inventory\Models\ChemicalRequest;
use App\Modules\Inventory\Models\FilmInventoryTransaction;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Models\LabShiftRegulatoryWork;
use App\Modules\Shifts\Models\LabShiftReport;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LabShiftService
{
    use RecordsAuditLogs;

    public function start(Employee $employee, ?string $comment = null, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Shift
    {
        return DB::transaction(function () use ($employee, $comment, $actor, $ipAddress, $userAgent): Shift {
            $this->ensureNoOpenShift($employee, ShiftType::Lab);

            $shift = Shift::query()->create([
                'employee_id' => $employee->getKey(),
                'object_id' => $employee->object_id,
                'type' => ShiftType::Lab,
                'status' => ShiftStatus::Open,
                'started_at' => now(),
                'comment' => $comment,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Shift::class,
                    entityId: $shift->getKey(),
                    operation: 'shift.lab.started',
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
    public function addReport(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): LabShiftReport
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): LabShiftReport {
            $report = $shift->labReport()->updateOrCreate(
                ['shift_id' => $shift->getKey()],
                [
                    'summary' => $data['summary'] ?? null,
                    'comment' => $data['comment'] ?? null,
                    'completed_at' => $data['completed_at'] ?? null,
                ],
            );

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: LabShiftReport::class,
                    entityId: $report->getKey(),
                    operation: 'lab_shift_report.saved',
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
     * @param  array{worked_at?: string|null, description: string, comment?: string|null}  $data
     */
    public function addRegulatoryWork(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): LabShiftRegulatoryWork
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): LabShiftRegulatoryWork {
            $work = $shift->labRegulatoryWorks()->create([
                'worked_at' => $data['worked_at'] ?? now(),
                'description' => $data['description'],
                'comment' => $data['comment'] ?? null,
            ]);

            $this->markInProgress($shift);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: LabShiftRegulatoryWork::class,
                    entityId: $work->getKey(),
                    operation: 'lab_shift_regulatory_work.created',
                    after: [
                        'id' => $work->getKey(),
                        'shift_id' => $work->shift_id,
                        'description' => $work->description,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $work;
        });
    }

    /**
     * @param  array{rt_film_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function receiveFilm(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): FilmInventoryTransaction
    {
        return app(InventoryService::class)->recordFilmTransaction($shift, 'received', $data, $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{rt_film_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function issueFilm(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): FilmInventoryTransaction
    {
        return app(InventoryService::class)->recordFilmTransaction($shift, 'issued', $data, $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{rt_film_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function writeOffFilm(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): FilmInventoryTransaction
    {
        return app(InventoryService::class)->recordFilmTransaction($shift, 'written_off', $data, $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{chemical_type_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function receiveChemical(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ChemicalInventoryTransaction
    {
        return app(InventoryService::class)->recordChemicalTransaction($shift, 'received', $data, $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{chemical_type_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function replaceChemical(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ChemicalInventoryTransaction
    {
        return app(InventoryService::class)->recordChemicalTransaction($shift, 'replaced', $data, $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{chemical_type_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function requestChemical(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ChemicalRequest
    {
        return app(InventoryService::class)->requestChemical($shift, $data, $actor, $ipAddress, $userAgent);
    }

    public function complete(Shift $shift, ?string $comment = null, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Shift
    {
        return DB::transaction(function () use ($shift, $comment, $actor, $ipAddress, $userAgent): Shift {
            if ($shift->labRegulatoryWorks()->count() === 0) {
                throw ValidationException::withMessages([
                    'completed_at' => 'Для завершения смены нужны регламентные работы.',
                ]);
            }

            if ($shift->labReport === null) {
                throw ValidationException::withMessages([
                    'report' => 'Для завершения смены нужен сменный отчет.',
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
                    operation: 'shift.lab.completed',
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
