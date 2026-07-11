<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\DTO;

/**
 * Structured payload for request creation with attached welds.
 */
final class NdtRequestFormData
{
    /**
     * @param  list<NdtRequestWeldData>  $welds
     */
    public function __construct(
        public readonly string $requestNumber,
        public readonly string $requestDate,
        public readonly int $objectId,
        public readonly ?int $organizationId,
        public readonly ?int $titleId,
        public readonly ?string $priority,
        public readonly ?string $dueDate,
        public readonly ?string $basis,
        public readonly ?string $comment,
        public readonly array $welds,
    ) {}

    /**
     * @param  array{
     *     request_number: string,
     *     request_date: string,
     *     object_id: int,
     *     organization_id?: int|null,
     *     title_id?: int|null,
     *     priority?: string|null,
     *     due_date?: string|null,
     *     basis?: string|null,
     *     comment?: string|null,
     *     welds?: array<int, array<string, mixed>>
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            requestNumber: $data['request_number'],
            requestDate: $data['request_date'],
            objectId: (int) $data['object_id'],
            organizationId: isset($data['organization_id']) && $data['organization_id'] !== '' ? (int) $data['organization_id'] : null,
            titleId: isset($data['title_id']) && $data['title_id'] !== '' ? (int) $data['title_id'] : null,
            priority: $data['priority'] ?? null,
            dueDate: $data['due_date'] ?? null,
            basis: $data['basis'] ?? null,
            comment: $data['comment'] ?? null,
            welds: array_map(
                static fn (array $row): NdtRequestWeldData => NdtRequestWeldData::fromArray($row),
                $data['welds'] ?? [],
            ),
        );
    }
}
