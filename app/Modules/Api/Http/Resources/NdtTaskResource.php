<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Modules\NdtTasks\Models\NdtTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NdtTask
 */
final class NdtTaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_number' => $this->task_number,
            'object_id' => $this->object_id,
            'ndt_request_id' => $this->ndt_request_id,
            'ndt_method_id' => $this->ndt_method_id,
            'assignee_employee_id' => $this->assignee_employee_id,
            'planned_date' => $this->planned_date?->toDateString(),
            'priority' => $this->priority,
            'comment' => $this->comment,
            'status' => $this->status->value,
            'request' => $this->whenLoaded('request', fn () => [
                'id' => $this->request->id,
                'request_number' => $this->request->request_number,
                'status' => $this->request->status->value,
            ]),
            'method' => $this->whenLoaded('method', fn () => [
                'id' => $this->method->id,
                'code' => $this->method->code->value,
                'name' => $this->method->name,
            ]),
            'assignee' => $this->whenLoaded('assigneeEmployee', fn () => [
                'id' => $this->assigneeEmployee->id,
                'name' => trim(implode(' ', array_filter([
                    $this->assigneeEmployee->last_name,
                    $this->assigneeEmployee->first_name,
                    $this->assigneeEmployee->middle_name,
                ]))),
            ]),
            'items' => $this->whenLoaded('items', fn (): array => $this->items->map(static fn ($item): NdtTaskItemResource => new NdtTaskItemResource($item))->values()->all(), []),
        ];
    }
}
