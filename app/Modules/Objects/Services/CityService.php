<?php

declare(strict_types=1);

namespace App\Modules\Objects\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Objects\Models\City;

final class CityService
{
    use RecordsAuditLogs;

    /**
     * @param  array{name: string, comment?: string|null, is_active?: bool}  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): City
    {
        $city = City::query()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: City::class,
                entityId: $city->getKey(),
                operation: 'city.created',
                after: $this->snapshot($city),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $city;
    }

    /**
     * @param  array{name?: string, comment?: string|null, is_active?: bool}  $data
     */
    public function update(City $city, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): City
    {
        $before = $this->snapshot($city);
        $city->fill($this->normalize($data, $city))->save();
        $city->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: City::class,
                entityId: $city->getKey(),
                operation: 'city.updated',
                before: $before,
                after: $this->snapshot($city),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $city;
    }

    public function deactivate(City $city, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): City
    {
        if (! $city->is_active) {
            return $city;
        }

        return $this->update($city, ['is_active' => false], $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{name?: string, comment?: string|null, is_active?: bool}  $data
     * @return array{name?: string, comment?: string|null, is_active?: bool}
     */
    private function normalize(array $data, ?City $city = null): array
    {
        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(City $city): array
    {
        return [
            'id' => $city->getKey(),
            'name' => $city->name,
            'is_active' => $city->is_active,
            'comment' => $city->comment,
        ];
    }
}
