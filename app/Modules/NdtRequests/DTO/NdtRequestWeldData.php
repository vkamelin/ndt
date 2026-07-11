<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\DTO;

/**
 * Structured payload for a weld row inside an NDT request.
 */
final class NdtRequestWeldData
{
    public function __construct(
        public readonly string $weldNumber,
        public readonly ?int $titleId = null,
        public readonly ?int $drawingId = null,
        public readonly ?int $lineId = null,
        public readonly ?string $diameter = null,
        public readonly ?string $thickness = null,
        public readonly ?int $material1Id = null,
        public readonly ?int $material2Id = null,
        public readonly ?string $weldedAt = null,
        public readonly ?int $weldingProcessId = null,
        public readonly ?int $weldTypeId = null,
        public readonly ?int $pipelineCategoryId = null,
        public readonly ?int $mediumId = null,
        public readonly ?bool $pwht = null,
        public readonly ?int $normativeDocumentId = null,
    ) {}

    /**
     * @param  array{
     *     weld_number: string,
     *     title_id?: int|null,
     *     drawing_id?: int|null,
     *     line_id?: int|null,
     *     diameter?: string|int|float|null,
     *     thickness?: string|int|float|null,
     *     material_1_id?: int|null,
     *     material_2_id?: int|null,
     *     welded_at?: string|null,
     *     welding_process_id?: int|null,
     *     weld_type_id?: int|null,
     *     pipeline_category_id?: int|null,
     *     medium_id?: int|null,
     *     pwht?: bool|int|string|null,
     *     normative_document_id?: int|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            weldNumber: trim((string) $data['weld_number']),
            titleId: isset($data['title_id']) && $data['title_id'] !== '' ? (int) $data['title_id'] : null,
            drawingId: isset($data['drawing_id']) && $data['drawing_id'] !== '' ? (int) $data['drawing_id'] : null,
            lineId: isset($data['line_id']) && $data['line_id'] !== '' ? (int) $data['line_id'] : null,
            diameter: isset($data['diameter']) && $data['diameter'] !== '' ? (string) $data['diameter'] : null,
            thickness: isset($data['thickness']) && $data['thickness'] !== '' ? (string) $data['thickness'] : null,
            material1Id: isset($data['material_1_id']) && $data['material_1_id'] !== '' ? (int) $data['material_1_id'] : null,
            material2Id: isset($data['material_2_id']) && $data['material_2_id'] !== '' ? (int) $data['material_2_id'] : null,
            weldedAt: isset($data['welded_at']) && $data['welded_at'] !== '' ? (string) $data['welded_at'] : null,
            weldingProcessId: isset($data['welding_process_id']) && $data['welding_process_id'] !== '' ? (int) $data['welding_process_id'] : null,
            weldTypeId: isset($data['weld_type_id']) && $data['weld_type_id'] !== '' ? (int) $data['weld_type_id'] : null,
            pipelineCategoryId: isset($data['pipeline_category_id']) && $data['pipeline_category_id'] !== '' ? (int) $data['pipeline_category_id'] : null,
            mediumId: isset($data['medium_id']) && $data['medium_id'] !== '' ? (int) $data['medium_id'] : null,
            pwht: self::toBoolean($data['pwht'] ?? null),
            normativeDocumentId: isset($data['normative_document_id']) && $data['normative_document_id'] !== '' ? (int) $data['normative_document_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestPayload(int $objectId): array
    {
        return [
            'object_id' => $objectId,
            'title_id' => $this->titleId,
            'drawing_id' => $this->drawingId,
            'line_id' => $this->lineId,
            'weld_number' => $this->weldNumber,
            'diameter' => $this->diameter,
            'thickness' => $this->thickness,
            'material_1_id' => $this->material1Id,
            'material_2_id' => $this->material2Id,
            'welded_at' => $this->weldedAt,
            'welding_process_id' => $this->weldingProcessId,
            'weld_type_id' => $this->weldTypeId,
            'pipeline_category_id' => $this->pipelineCategoryId,
            'medium_id' => $this->mediumId,
            'pwht' => $this->pwht,
            'normative_document_id' => $this->normativeDocumentId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'weld_number' => $this->weldNumber,
            'title_id' => $this->titleId,
            'drawing_id' => $this->drawingId,
            'line_id' => $this->lineId,
            'diameter' => $this->diameter,
            'thickness' => $this->thickness,
            'material_1_id' => $this->material1Id,
            'material_2_id' => $this->material2Id,
            'welded_at' => $this->weldedAt,
            'welding_process_id' => $this->weldingProcessId,
            'weld_type_id' => $this->weldTypeId,
            'pipeline_category_id' => $this->pipelineCategoryId,
            'medium_id' => $this->mediumId,
            'pwht' => $this->pwht,
            'normative_document_id' => $this->normativeDocumentId,
        ];
    }

    private static function toBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $normalized = mb_strtolower(trim($value));

            return match ($normalized) {
                'да', 'yes', 'true', '1' => true,
                'нет', 'no', 'false', '0' => false,
                default => filter_var($normalized, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
            };
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
    }
}
