<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\DTO;

/**
 * Structured payload for the MT form.
 */
final class MagneticControlData
{
    public function __construct(
        public readonly ?string $conclusionNumber,
        public readonly ?string $conclusionDate,
        public readonly ?string $controlZone,
        public readonly ?string $material,
        public readonly ?string $controlParameters,
        public readonly ?string $transferRegisterNumber,
        public readonly ?string $actNumber,
    ) {}

    /**
     * @param  array{
     *     conclusion_number?: string|null,
     *     conclusion_date?: string|null,
     *     control_zone?: string|null,
     *     material?: string|null,
     *     control_parameters?: string|null,
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
            material: $data['material'] ?? null,
            controlParameters: $data['control_parameters'] ?? null,
            transferRegisterNumber: $data['transfer_register_number'] ?? null,
            actNumber: $data['act_number'] ?? null,
        );
    }
}
