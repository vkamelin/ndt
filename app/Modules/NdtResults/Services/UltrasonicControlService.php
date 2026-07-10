<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Services;

use App\Models\User;
use App\Modules\NdtResults\DTO\UltrasonicControlData;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtResults\Models\UtResult;

final class UltrasonicControlService
{
    /**
     * Save or update the UT form for a result.
     */
    public function save(NdtResult $result, UltrasonicControlData $data, ?User $actor = null): UtResult
    {
        unset($actor);

        return UtResult::query()->updateOrCreate(
            ['ndt_result_id' => $result->getKey()],
            [
                'conclusion_number' => $data->conclusionNumber,
                'conclusion_date' => $data->conclusionDate,
                'sounding_scheme' => $data->soundingScheme,
                'transducer' => $data->transducer,
                'tuning_parameters' => $data->tuningParameters,
                'transfer_register_number' => $data->transferRegisterNumber,
                'act_number' => $data->actNumber,
            ],
        );
    }
}
