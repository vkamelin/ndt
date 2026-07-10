<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMobileTaskItemResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ndt_results.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'ndt_method_id' => ['required', 'integer', 'exists:ndt_methods,id'],
            'executor_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
            'normative_document_id' => ['nullable', 'integer', 'exists:normative_documents,id'],
            'control_date' => ['required', 'date'],
            'result_text' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
