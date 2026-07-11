<?php

declare(strict_types=1);

namespace App\Modules\Registers\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Registers\Enums\TransferRegisterStatus;
use App\Modules\Registers\Models\Act;
use App\Modules\Registers\Models\TransferRegister;
use App\Modules\Registers\Models\TransferRegisterItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class TransferRegisterService
{
    use RecordsAuditLogs;

    /**
     * @param  array{
     *     register_type_id: int,
     *     number: string,
     *     date: string,
     *     city_id: int,
     *     object_id: int,
     *     sender_employee_id: int,
     *     receiver_employee_id: int,
     *     status: string,
     *     comment?: string|null
     * }  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegister
    {
        return DB::transaction(function () use ($data, $actor, $ipAddress, $userAgent): TransferRegister {
            $register = TransferRegister::query()->create($this->normalize($data));

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: TransferRegister::class,
                    entityId: $register->getKey(),
                    operation: 'transfer_register.created',
                    after: $this->snapshot($register->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $register;
        });
    }

    /**
     * @param  array{
     *     register_type_id: int,
     *     number: string,
     *     date: string,
     *     city_id: int,
     *     object_id: int,
     *     sender_employee_id: int,
     *     receiver_employee_id: int,
     *     status: string,
     *     comment?: string|null
     * }  $data
     */
    public function update(TransferRegister $register, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegister
    {
        $this->ensureEditable($register);

        return DB::transaction(function () use ($register, $data, $actor, $ipAddress, $userAgent): TransferRegister {
            $before = $this->snapshot($register);
            $register->fill($this->normalize($data))->save();
            $register->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: TransferRegister::class,
                    entityId: $register->getKey(),
                    operation: 'transfer_register.updated',
                    before: $before,
                    after: $this->snapshot($register),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $register;
        });
    }

    /**
     * @param  array{
     *     related_type: string,
     *     related_id: int,
     *     file_id?: int|null,
     *     sort_order?: int|null,
     *     comment?: string|null
     * }  $data
     */
    public function addItem(TransferRegister $register, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegisterItem
    {
        return DB::transaction(function () use ($register, $data, $actor, $ipAddress, $userAgent): TransferRegisterItem {
            $this->ensureRelatedModelExists($data['related_type'], (int) $data['related_id']);

            $existing = $register->items()
                ->where('related_type', $data['related_type'])
                ->where('related_id', $data['related_id'])
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $item = $register->items()->create([
                'related_type' => $data['related_type'],
                'related_id' => $data['related_id'],
                'file_id' => $data['file_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? 1,
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: TransferRegisterItem::class,
                    entityId: $item->getKey(),
                    operation: 'transfer_register.item.created',
                    after: [
                        'id' => $item->getKey(),
                        'transfer_register_id' => $item->transfer_register_id,
                        'related_type' => $item->related_type,
                        'related_id' => $item->related_id,
                        'file_id' => $item->file_id,
                        'sort_order' => $item->sort_order,
                        'comment' => $item->comment,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $item;
        });
    }

    public function form(TransferRegister $register, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegister
    {
        return $this->transition($register, TransferRegisterStatus::Formed, [TransferRegisterStatus::Draft, TransferRegisterStatus::Returned], 'transfer_register.formed', $actor, $comment, $ipAddress, $userAgent);
    }

    public function send(TransferRegister $register, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegister
    {
        return $this->transition($register, TransferRegisterStatus::Sent, [TransferRegisterStatus::Formed, TransferRegisterStatus::Returned], 'transfer_register.sent', $actor, $comment, $ipAddress, $userAgent);
    }

    public function accept(TransferRegister $register, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegister
    {
        return $this->transition($register, TransferRegisterStatus::Accepted, [TransferRegisterStatus::Sent, TransferRegisterStatus::Returned], 'transfer_register.accepted', $actor, $comment, $ipAddress, $userAgent);
    }

    public function returnRegister(TransferRegister $register, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegister
    {
        return $this->transition($register, TransferRegisterStatus::Returned, [TransferRegisterStatus::Formed, TransferRegisterStatus::Sent, TransferRegisterStatus::Accepted], 'transfer_register.returned', $actor, $comment, $ipAddress, $userAgent);
    }

    public function close(TransferRegister $register, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): TransferRegister
    {
        return $this->transition($register, TransferRegisterStatus::Closed, [TransferRegisterStatus::Accepted, TransferRegisterStatus::Returned], 'transfer_register.closed', $actor, $comment, $ipAddress, $userAgent);
    }

    /**
     * @param  array{
     *     act_type_id: int,
     *     number: string,
     *     date: string,
     *     city_id: int,
     *     object_id: int,
     *     comment?: string|null
     * }  $data
     */
    public function createAct(TransferRegister $register, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Act
    {
        return DB::transaction(function () use ($register, $data, $actor, $ipAddress, $userAgent): Act {
            $act = $register->acts()->create($this->normalizeAct($data));

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: Act::class,
                    entityId: $act->getKey(),
                    operation: 'act.created',
                    after: $this->actSnapshot($act),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $act;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        return [
            'register_type_id' => $data['register_type_id'],
            'number' => $data['number'],
            'date' => $data['date'],
            'city_id' => $data['city_id'],
            'object_id' => $data['object_id'],
            'sender_employee_id' => $data['sender_employee_id'],
            'receiver_employee_id' => $data['receiver_employee_id'],
            'status' => TransferRegisterStatus::from($data['status']),
            'comment' => $data['comment'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeAct(array $data): array
    {
        return [
            'act_type_id' => $data['act_type_id'],
            'number' => $data['number'],
            'date' => $data['date'],
            'city_id' => $data['city_id'],
            'object_id' => $data['object_id'],
            'comment' => $data['comment'] ?? null,
        ];
    }

    private function ensureEditable(TransferRegister $register): void
    {
        if (! $register->status->canBeEdited()) {
            throw ValidationException::withMessages([
                'status' => 'Редактирование доступно только для черновика или возвращенного реестра.',
            ]);
        }
    }

    private function transition(
        TransferRegister $register,
        TransferRegisterStatus $toStatus,
        array $allowedStatuses,
        string $operation,
        ?User $actor = null,
        ?string $comment = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TransferRegister {
        if (! in_array($register->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Нельзя перевести реестр в этот статус из текущего состояния.',
            ]);
        }

        return DB::transaction(function () use ($register, $toStatus, $operation, $actor, $comment, $ipAddress, $userAgent): TransferRegister {
            $before = $this->snapshot($register);

            $register->forceFill([
                'status' => $toStatus->value,
            ])->save();
            $register->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: TransferRegister::class,
                    entityId: $register->getKey(),
                    operation: $operation,
                    before: $before,
                    after: $this->snapshot($register),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $register;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(TransferRegister $register): array
    {
        return [
            'id' => $register->getKey(),
            'register_type_id' => $register->register_type_id,
            'number' => $register->number,
            'date' => $register->date?->toDateString(),
            'city_id' => $register->city_id,
            'object_id' => $register->object_id,
            'sender_employee_id' => $register->sender_employee_id,
            'receiver_employee_id' => $register->receiver_employee_id,
            'status' => $register->status->value,
            'comment' => $register->comment,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function actSnapshot(Act $act): array
    {
        return [
            'id' => $act->getKey(),
            'act_type_id' => $act->act_type_id,
            'transfer_register_id' => $act->transfer_register_id,
            'number' => $act->number,
            'date' => $act->date?->toDateString(),
            'city_id' => $act->city_id,
            'object_id' => $act->object_id,
            'comment' => $act->comment,
        ];
    }

    private function ensureRelatedModelExists(string $relatedType, int $relatedId): void
    {
        if (! class_exists($relatedType)) {
            throw ValidationException::withMessages([
                'related_type' => 'Связанная сущность не найдена.',
            ]);
        }

        if (! is_subclass_of($relatedType, Model::class)) {
            throw ValidationException::withMessages([
                'related_type' => 'Связанная сущность не поддерживается.',
            ]);
        }

        $query = $relatedType::query();
        if ($query->whereKey($relatedId)->doesntExist()) {
            throw ValidationException::withMessages([
                'related_id' => 'Связанная сущность не найдена.',
            ]);
        }
    }
}
