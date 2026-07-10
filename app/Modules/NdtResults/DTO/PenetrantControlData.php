<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\DTO;

/**
 * Structured payload for the PT form.
 */
final class PenetrantControlData
{
    public function __construct(
        public readonly ?string $conclusionNumber,
        public readonly ?string $conclusionDate,
        public readonly ?string $controlZone,
        public readonly ?string $materialsUsed,
        public readonly ?string $transferRegisterNumber,
        public readonly ?string $actNumber,
    ) {
    }

    /**
     * @param  array{
     *     conclusion_number?: string|null,
     *     conclusion_date?: string|null,
     *     control_zone?: string|null,
     *     materials_used?: string|null,
     *     transfer_register_number?: string|null,
     *     act_number?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            conclusionNumber: $data['conclusion_number'] ?? null,
            conclusionDate: $data['conclusion_date'] ?? null,
            controlZone: $data['control_zone'] ?? null,
            materialsUsed: $data['materials_used'] ?? null,
            transferRegisterNumber: $data['transfer_register_number'] ?? null,
            actNumber: $data['act_number'] ?? null,
        );
    }
}
