<?php

declare(strict_types=1);

namespace App\Modules\Welds\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Welds\Models\Welder;

final class WelderService
{
    use RecordsAuditLogs;

    /**
     * @param  array{employee_id?: int|null, name: string, stamp: string, comment?: string|null, is_active?: bool}  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Welder
    {
        $welder = Welder::query()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Welder::class,
                entityId: $welder->getKey(),
                operation: 'welder.created',
                after: $this->snapshot($welder),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $welder;
    }

    /**
     * @param  array{employee_id?: int|null, name?: string, stamp?: string, comment?: string|null, is_active?: bool}  $data
     */
    public function update(Welder $welder, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Welder
    {
        $before = $this->snapshot($welder);
        $welder->fill($this->normalize($data))->save();
        $welder->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Welder::class,
                entityId: $welder->getKey(),
                operation: 'welder.updated',
                before: $before,
                after: $this->snapshot($welder),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $welder;
    }

    public function deactivate(Welder $welder, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Welder
    {
        if (! $welder->is_active) {
            return $welder;
        }

        return $this->update($welder, ['is_active' => false], $actor, $ipAddress, $userAgent);
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
    private function snapshot(Welder $welder): array
    {
        return [
            'id' => $welder->getKey(),
            'employee_id' => $welder->employee_id,
            'name' => $welder->name,
            'stamp' => $welder->stamp,
            'is_active' => $welder->is_active,
            'comment' => $welder->comment,
        ];
    }
}
