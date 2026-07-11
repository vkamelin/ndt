<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Equipment\Enums\EquipmentStatus;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Equipment\Models\EquipmentAssignment;
use App\Modules\Equipment\Models\EquipmentCalibration;
use App\Modules\Equipment\Models\EquipmentDefect;
use App\Modules\Equipment\Models\EquipmentDocument;
use App\Modules\Equipment\Models\EquipmentMovement;
use App\Modules\Equipment\Models\EquipmentRepair;
use App\Modules\Equipment\Models\EquipmentVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EquipmentService
{
    use RecordsAuditLogs;

    /**
     * @param  array{equipment_type_id: int, object_id: int, name: string, inventory_number?: string|null, serial_number?: string|null, manufacturer?: string|null, model?: string|null, status: string, purchased_at?: string|null, comment?: string|null}  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Equipment
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): Equipment {
            $equipment = Equipment::query()->create($this->normalize($data));

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Equipment::class,
                    entityId: $equipment->getKey(),
                    operation: 'equipment.created',
                    after: $this->snapshot($equipment->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $equipment->refresh();
        });
    }

    /**
     * @param  array{equipment_type_id?: int, object_id?: int, name?: string, inventory_number?: string|null, serial_number?: string|null, manufacturer?: string|null, model?: string|null, status?: string, purchased_at?: string|null, comment?: string|null}  $data
     */
    public function update(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Equipment
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): Equipment {
            $before = $this->snapshot($equipment);
            $equipment->fill($this->normalize($data, false))->save();
            $equipment->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Equipment::class,
                    entityId: $equipment->getKey(),
                    operation: 'equipment.updated',
                    before: $before,
                    after: $this->snapshot($equipment),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $equipment;
        });
    }

    public function writeOff(Equipment $equipment, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): Equipment
    {
        if ($equipment->status === EquipmentStatus::WrittenOff) {
            return $equipment;
        }

        return DB::transaction(function () use ($equipment, $actor, $comment, $ipAddress, $userAgent): Equipment {
            $before = $this->snapshot($equipment);
            $equipment->fill([
                'status' => EquipmentStatus::WrittenOff,
                'write_off_at' => today(),
            ])->save();
            $equipment->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Equipment::class,
                    entityId: $equipment->getKey(),
                    operation: 'equipment.written_off',
                    before: $before,
                    after: $this->snapshot($equipment),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $equipment;
        });
    }

    /**
     * @param  array{verified_at: string, valid_until?: string|null, certificate_number?: string|null, comment?: string|null}  $data
     */
    public function recordVerification(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentVerification
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): EquipmentVerification {
            $verification = $equipment->verifications()->create([
                'recorded_by_user_id' => $actor?->getKey(),
                'verified_at' => $data['verified_at'],
                'valid_until' => $data['valid_until'] ?? null,
                'certificate_number' => $data['certificate_number'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentVerification::class,
                    entityId: $verification->getKey(),
                    operation: 'equipment.verification.created',
                    after: $this->verificationSnapshot($verification),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $verification;
        });
    }

    /**
     * @param  array{calibrated_at: string, valid_until?: string|null, certificate_number?: string|null, comment?: string|null}  $data
     */
    public function recordCalibration(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentCalibration
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): EquipmentCalibration {
            $calibration = $equipment->calibrations()->create([
                'recorded_by_user_id' => $actor?->getKey(),
                'calibrated_at' => $data['calibrated_at'],
                'valid_until' => $data['valid_until'] ?? null,
                'certificate_number' => $data['certificate_number'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentCalibration::class,
                    entityId: $calibration->getKey(),
                    operation: 'equipment.calibration.created',
                    after: $this->calibrationSnapshot($calibration),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $calibration;
        });
    }

    /**
     * @param  array{started_at: string, completed_at?: string|null, description: string, comment?: string|null}  $data
     */
    public function recordRepair(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentRepair
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): EquipmentRepair {
            $repair = $equipment->repairs()->create([
                'recorded_by_user_id' => $actor?->getKey(),
                'started_at' => $data['started_at'],
                'completed_at' => $data['completed_at'] ?? null,
                'description' => $data['description'],
                'comment' => $data['comment'] ?? null,
            ]);

            $equipment->fill([
                'status' => $repair->completed_at === null ? EquipmentStatus::InRepair : EquipmentStatus::Available,
            ])->save();
            $equipment->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentRepair::class,
                    entityId: $repair->getKey(),
                    operation: 'equipment.repair.created',
                    after: $this->repairSnapshot($repair),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $repair;
        });
    }

    /**
     * @param  array{employee_id: int, issued_at: string, comment?: string|null}  $data
     */
    public function issue(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentAssignment
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): EquipmentAssignment {
            if ($equipment->currentAssignment !== null) {
                throw ValidationException::withMessages([
                    'employee_id' => 'У оборудования уже есть открытая выдача.',
                ]);
            }

            $assignment = $equipment->assignments()->create([
                'employee_id' => $data['employee_id'],
                'recorded_by_user_id' => $actor?->getKey(),
                'issued_at' => $data['issued_at'],
                'comment' => $data['comment'] ?? null,
            ]);

            $equipment->fill([
                'status' => EquipmentStatus::Issued,
            ])->save();
            $equipment->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentAssignment::class,
                    entityId: $assignment->getKey(),
                    operation: 'equipment.assignment.created',
                    after: $this->assignmentSnapshot($assignment),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $assignment;
        });
    }

    public function returnAssignment(EquipmentAssignment $assignment, ?User $actor = null, ?string $returnedAt = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentAssignment
    {
        return DB::transaction(function () use ($assignment, $actor, $returnedAt, $comment, $ipAddress, $userAgent): EquipmentAssignment {
            if ($assignment->returned_at !== null) {
                return $assignment;
            }

            $before = $this->assignmentSnapshot($assignment);
            $assignment->fill([
                'returned_at' => $returnedAt ?? today()->toDateString(),
                'comment' => $comment ?? $assignment->comment,
            ])->save();
            $assignment->refresh();

            $equipment = $assignment->equipment()->firstOrFail();
            if ($equipment->status === EquipmentStatus::Issued) {
                $equipment->fill([
                    'status' => EquipmentStatus::Available,
                ])->save();
            }

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentAssignment::class,
                    entityId: $assignment->getKey(),
                    operation: 'equipment.assignment.returned',
                    before: $before,
                    after: $this->assignmentSnapshot($assignment),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $assignment;
        });
    }

    /**
     * @param  array{from_object_id?: int|null, to_object_id: int, moved_at: string, comment?: string|null}  $data
     */
    public function recordMovement(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentMovement
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): EquipmentMovement {
            $movement = $equipment->movements()->create([
                'from_object_id' => $data['from_object_id'] ?? $equipment->object_id,
                'to_object_id' => $data['to_object_id'],
                'recorded_by_user_id' => $actor?->getKey(),
                'moved_at' => $data['moved_at'],
                'comment' => $data['comment'] ?? null,
            ]);

            $equipment->fill([
                'object_id' => $data['to_object_id'],
            ])->save();
            $equipment->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentMovement::class,
                    entityId: $movement->getKey(),
                    operation: 'equipment.moved',
                    after: $this->movementSnapshot($movement),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $movement;
        });
    }

    /**
     * @param  array{detected_at: string, description: string, comment?: string|null}  $data
     */
    public function recordDefect(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentDefect
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): EquipmentDefect {
            $defect = $equipment->defects()->create([
                'recorded_by_user_id' => $actor?->getKey(),
                'detected_at' => $data['detected_at'],
                'description' => $data['description'],
                'comment' => $data['comment'] ?? null,
            ]);

            $equipment->fill([
                'status' => EquipmentStatus::Defective,
            ])->save();
            $equipment->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentDefect::class,
                    entityId: $defect->getKey(),
                    operation: 'equipment.defect.created',
                    after: $this->defectSnapshot($defect),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $defect;
        });
    }

    /**
     * @param  array{document_name: string, document_number?: string|null, issued_at?: string|null, valid_until?: string|null, comment?: string|null}  $data
     */
    public function addDocument(Equipment $equipment, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): EquipmentDocument
    {
        return DB::transaction(function () use ($equipment, $data, $actor, $ipAddress, $userAgent): EquipmentDocument {
            $document = $equipment->documents()->create([
                'recorded_by_user_id' => $actor?->getKey(),
                'document_name' => $data['document_name'],
                'document_number' => $data['document_number'] ?? null,
                'issued_at' => $data['issued_at'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: EquipmentDocument::class,
                    entityId: $document->getKey(),
                    operation: 'equipment.document.created',
                    after: $this->documentSnapshot($document),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $document;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(Equipment $equipment): array
    {
        return [
            'id' => $equipment->getKey(),
            'equipment_type_id' => $equipment->equipment_type_id,
            'object_id' => $equipment->object_id,
            'name' => $equipment->name,
            'inventory_number' => $equipment->inventory_number,
            'serial_number' => $equipment->serial_number,
            'status' => $equipment->status instanceof EquipmentStatus ? $equipment->status->value : $equipment->status,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, bool $creating = true): array
    {
        if ($creating && ! array_key_exists('status', $data)) {
            $data['status'] = EquipmentStatus::Available->value;
        }

        if (isset($data['status']) && $data['status'] instanceof EquipmentStatus) {
            $data['status'] = $data['status']->value;
        }

        if (isset($data['status']) && is_string($data['status'])) {
            $data['status'] = EquipmentStatus::from($data['status']);
        }

        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function verificationSnapshot(EquipmentVerification $verification): array
    {
        return [
            'id' => $verification->getKey(),
            'equipment_id' => $verification->equipment_id,
            'verified_at' => $verification->verified_at?->toDateString(),
            'valid_until' => $verification->valid_until?->toDateString(),
            'certificate_number' => $verification->certificate_number,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function calibrationSnapshot(EquipmentCalibration $calibration): array
    {
        return [
            'id' => $calibration->getKey(),
            'equipment_id' => $calibration->equipment_id,
            'calibrated_at' => $calibration->calibrated_at?->toDateString(),
            'valid_until' => $calibration->valid_until?->toDateString(),
            'certificate_number' => $calibration->certificate_number,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function repairSnapshot(EquipmentRepair $repair): array
    {
        return [
            'id' => $repair->getKey(),
            'equipment_id' => $repair->equipment_id,
            'started_at' => $repair->started_at?->toDateString(),
            'completed_at' => $repair->completed_at?->toDateString(),
            'description' => $repair->description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function assignmentSnapshot(EquipmentAssignment $assignment): array
    {
        return [
            'id' => $assignment->getKey(),
            'equipment_id' => $assignment->equipment_id,
            'employee_id' => $assignment->employee_id,
            'issued_at' => $assignment->issued_at?->toDateString(),
            'returned_at' => $assignment->returned_at?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function movementSnapshot(EquipmentMovement $movement): array
    {
        return [
            'id' => $movement->getKey(),
            'equipment_id' => $movement->equipment_id,
            'from_object_id' => $movement->from_object_id,
            'to_object_id' => $movement->to_object_id,
            'moved_at' => $movement->moved_at?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function defectSnapshot(EquipmentDefect $defect): array
    {
        return [
            'id' => $defect->getKey(),
            'equipment_id' => $defect->equipment_id,
            'detected_at' => $defect->detected_at?->toDateString(),
            'description' => $defect->description,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function documentSnapshot(EquipmentDocument $document): array
    {
        return [
            'id' => $document->getKey(),
            'equipment_id' => $document->equipment_id,
            'document_name' => $document->document_name,
            'document_number' => $document->document_number,
            'issued_at' => $document->issued_at?->toDateString(),
            'valid_until' => $document->valid_until?->toDateString(),
        ];
    }
}
