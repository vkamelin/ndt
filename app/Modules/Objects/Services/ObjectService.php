<?php

declare(strict_types=1);

namespace App\Modules\Objects\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Objects\Models\NdtObject;

final class ObjectService
{
    use RecordsAuditLogs;

    /**
     * @param  array{city_id: int, organization_id?: int|null, name: string, code?: string|null, comment?: string|null, is_active?: bool}  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtObject
    {
        $object = NdtObject::query()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtObject::class,
                entityId: $object->getKey(),
                operation: 'object.created',
                after: $this->snapshot($object),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $object;
    }

    /**
     * @param  array{city_id?: int, organization_id?: int|null, name?: string, code?: string|null, comment?: string|null, is_active?: bool}  $data
     */
    public function update(NdtObject $object, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtObject
    {
        $before = $this->snapshot($object);
        $object->fill($this->normalize($data))->save();
        $object->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: NdtObject::class,
                entityId: $object->getKey(),
                operation: 'object.updated',
                before: $before,
                after: $this->snapshot($object),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $object;
    }

    public function deactivate(NdtObject $object, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): NdtObject
    {
        if (! $object->is_active) {
            return $object;
        }

        return $this->update($object, ['is_active' => false], $actor, $ipAddress, $userAgent);
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
    private function snapshot(NdtObject $object): array
    {
        return [
            'id' => $object->getKey(),
            'city_id' => $object->city_id,
            'organization_id' => $object->organization_id,
            'name' => $object->name,
            'code' => $object->code,
            'is_active' => $object->is_active,
            'comment' => $object->comment,
        ];
    }
}
