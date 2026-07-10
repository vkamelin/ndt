<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\DTO;

/**
 * Structured payload for the UT form.
 */
final class UltrasonicControlData
{
    public function __construct(
        public readonly ?string $conclusionNumber,
        public readonly ?string $conclusionDate,
        public readonly ?string $soundingScheme,
        public readonly ?string $transducer,
        public readonly ?string $tuningParameters,
        public readonly ?string $transferRegisterNumber,
        public readonly ?string $actNumber,
    ) {
    }

    /**
     * @param  array{
     *     conclusion_number?: string|null,
     *     conclusion_date?: string|null,
     *     sounding_scheme?: string|null,
     *     transducer?: string|null,
     *     tuning_parameters?: string|null,
     *     transfer_register_number?: string|null,
     *     act_number?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            conclusionNumber: $data['conclusion_number'] ?? null,
            conclusionDate: $data['conclusion_date'] ?? null,
            soundingScheme: $data['sounding_scheme'] ?? null,
            transducer: $data['transducer'] ?? null,
            tuningParameters: $data['tuning_parameters'] ?? null,
            transferRegisterNumber: $data['transfer_register_number'] ?? null,
            actNumber: $data['act_number'] ?? null,
        );
    }
}
