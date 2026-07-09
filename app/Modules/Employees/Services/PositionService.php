<?php

declare(strict_types=1);

namespace App\Modules\Employees\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Employees\Models\Position;

final class PositionService
{
    use RecordsAuditLogs;

    /**
     * @param  array{name: string, comment?: string|null, is_active?: bool}  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Position
    {
        $position = Position::query()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Position::class,
                entityId: $position->getKey(),
                operation: 'position.created',
                after: $this->snapshot($position),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $position;
    }

    /**
     * @param  array{name?: string, comment?: string|null, is_active?: bool}  $data
     */
    public function update(Position $position, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Position
    {
        $before = $this->snapshot($position);
        $position->fill($this->normalize($data))->save();
        $position->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Position::class,
                entityId: $position->getKey(),
                operation: 'position.updated',
                before: $before,
                after: $this->snapshot($position),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $position;
    }

    public function deactivate(Position $position, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Position
    {
        if (! $position->is_active) {
            return $position;
        }

        return $this->update($position, ['is_active' => false], $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Position $position): array
    {
        return [
            'id' => $position->getKey(),
            'name' => $position->name,
            'is_active' => $position->is_active,
            'comment' => $position->comment,
        ];
    }
}
