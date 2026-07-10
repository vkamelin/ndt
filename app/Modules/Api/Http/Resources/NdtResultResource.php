<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Modules\NdtResults\Models\NdtResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NdtResult
 */
final class NdtResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ndt_task_id' => $this->ndt_task_id,
            'weld_id' => $this->weld_id,
            'ndt_method_id' => $this->ndt_method_id,
            'executor_employee_id' => $this->executor_employee_id,
            'equipment_id' => $this->equipment_id,
            'normative_document_id' => $this->normative_document_id,
            'control_date' => $this->control_date?->toDateString(),
            'analyzed_at' => $this->analyzed_at?->toAtomString(),
            'result_text' => $this->result_text,
            'comment' => $this->comment,
            'status' => $this->status->value,
            'weld' => $this->whenLoaded('weld', fn () => [
                'id' => $this->weld->id,
                'weld_number' => $this->weld->weld_number,
            ]),
            'method' => $this->whenLoaded('method', fn () => [
                'id' => $this->method->id,
                'code' => $this->method->code->value,
                'name' => $this->method->name,
            ]),
        ];
    }
}
