<?php

declare(strict_types=1);

namespace App\Modules\Audit\DTO;

use App\Models\User;

/**
 * Payload for a single audit log entry.
 */
final class AuditData
{
    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    public function __construct(
        public readonly string $entityType,
        public readonly int $entityId,
        public readonly string $operation,
        public readonly array $before = [],
        public readonly array $after = [],
        public readonly ?User $actor = null,
        public readonly ?string $reason = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    public static function forModelChange(
        string $entityType,
        int $entityId,
        string $operation,
        array $before = [],
        array $after = [],
        ?User $actor = null,
        ?string $reason = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): self {
        return new self(
            entityType: $entityType,
            entityId: $entityId,
            operation: $operation,
            before: $before,
            after: $after,
            actor: $actor,
            reason: $reason,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );
    }
}
