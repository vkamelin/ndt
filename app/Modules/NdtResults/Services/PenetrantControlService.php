<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Services;

use App\Models\User;
use App\Modules\NdtResults\DTO\PenetrantControlData;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtResults\Models\PtResult;

final class PenetrantControlService
{
    /**
     * Save or update the PT form for a result.
     */
    public function save(NdtResult $result, PenetrantControlData $data, ?User $actor = null): PtResult
    {
        unset($actor);

        return PtResult::query()->updateOrCreate(
            ['ndt_result_id' => $result->getKey()],
            [
                'conclusion_number' => $data->conclusionNumber,
                'conclusion_date' => $data->conclusionDate,
                'control_zone' => $data->controlZone,
                'materials_used' => $data->materialsUsed,
                'transfer_register_number' => $data->transferRegisterNumber,
                'act_number' => $data->actNumber,
            ],
        );
    }
}
