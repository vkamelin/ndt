<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Requests;

use App\Modules\Documents\Enums\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document !== null && $this->user()?->can('manage', $document) === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'number' => ['nullable', 'string', 'max:255'],
            'document_date' => ['required', 'date'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'object_id' => ['nullable', 'integer', 'exists:objects,id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
            'ndt_request_id' => ['nullable', 'integer', 'exists:ndt_requests,id'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(DocumentStatus::class)],
            'comment' => ['nullable', 'string'],
        ];
    }
}
