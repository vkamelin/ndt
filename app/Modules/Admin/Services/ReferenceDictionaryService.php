<?php

declare(strict_types=1);

namespace App\Modules\Admin\Services;

use App\Models\User;
use App\Modules\Admin\Models\AbstractDictionary;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;

final class ReferenceDictionaryService
{
    use RecordsAuditLogs;

    /**
     * @param  class-string<AbstractDictionary>  $modelClass
     */
    public function create(string $modelClass, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): AbstractDictionary
    {
        /** @var AbstractDictionary $entry */
        $entry = $modelClass::query()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: $modelClass,
                entityId: $entry->getKey(),
                operation: 'dictionary.created',
                after: $this->snapshot($entry),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $entry;
    }

    /**
     * @param  class-string<AbstractDictionary>  $modelClass
     */
    public function update(AbstractDictionary $entry, string $modelClass, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): AbstractDictionary
    {
        $before = $this->snapshot($entry);
        $entry->fill($this->normalize($data))->save();
        $entry->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: $modelClass,
                entityId: $entry->getKey(),
                operation: 'dictionary.updated',
                before: $before,
                after: $this->snapshot($entry),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $entry;
    }

    /**
     * @param  class-string<AbstractDictionary>  $modelClass
     */
    public function deactivate(AbstractDictionary $entry, string $modelClass, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): AbstractDictionary
    {
        if (! $entry->is_active) {
            return $entry;
        }

        return $this->update($entry, $modelClass, ['is_active' => false], $actor, $ipAddress, $userAgent);
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
    private function snapshot(AbstractDictionary $entry): array
    {
        return [
            'id' => $entry->getKey(),
            'name' => $entry->name,
            'is_active' => $entry->is_active,
            'comment' => $entry->comment,
        ];
    }
}
