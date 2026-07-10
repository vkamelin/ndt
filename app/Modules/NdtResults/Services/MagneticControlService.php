<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Services;

use App\Models\User;
use App\Modules\NdtResults\DTO\MagneticControlData;
use App\Modules\NdtResults\Models\MtResult;
use App\Modules\NdtResults\Models\NdtResult;

final class MagneticControlService
{
    /**
     * Save or update the MT form for a result.
     */
    public function save(NdtResult $result, MagneticControlData $data, ?User $actor = null): MtResult
    {
        unset($actor);

        return MtResult::query()->updateOrCreate(
            ['ndt_result_id' => $result->getKey()],
            [
                'conclusion_number' => $data->conclusionNumber,
                'conclusion_date' => $data->conclusionDate,
                'control_zone' => $data->controlZone,
                'material' => $data->material,
                'control_parameters' => $data->controlParameters,
                'transfer_register_number' => $data->transferRegisterNumber,
                'act_number' => $data->actNumber,
            ],
        );
    }
}
