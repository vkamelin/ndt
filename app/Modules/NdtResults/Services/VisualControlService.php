<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Services;

use App\Models\User;
use App\Modules\NdtResults\DTO\VisualControlData;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtResults\Models\VtResult;

final class VisualControlService
{
    /**
     * Save or update the VIK form for a result.
     */
    public function save(NdtResult $result, VisualControlData $data, ?User $actor = null): VtResult
    {
        unset($actor);

        return VtResult::query()->updateOrCreate(
            ['ndt_result_id' => $result->getKey()],
            [
                'conclusion_number' => $data->conclusionNumber,
                'conclusion_date' => $data->conclusionDate,
                'measurements' => $data->measurements,
                'transfer_register_number' => $data->transferRegisterNumber,
                'act_number' => $data->actNumber,
            ],
        );
    }
}
