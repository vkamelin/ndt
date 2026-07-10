<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNdtTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'task_number' => ['required', 'string', 'max:255', 'unique:ndt_tasks,task_number,'.$this->route('ndtTask')?->getKey()],
            'ndt_request_id' => ['required', 'integer', 'exists:ndt_requests,id'],
            'object_id' => ['required', 'integer', 'exists:objects,id'],
            'ndt_method_id' => ['required', 'integer', 'exists:ndt_methods,id'],
            'assignee_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'planned_date' => ['required', 'date'],
            'priority' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'weld_ids' => ['required', 'array', 'min:1'],
            'weld_ids.*' => ['integer', 'exists:welds,id'],
        ];
    }
}
