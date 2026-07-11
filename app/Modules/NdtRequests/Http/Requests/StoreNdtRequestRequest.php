<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNdtRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! ($this->user()?->can('ndt_requests.manage') ?? false)) {
            return false;
        }

        return $this->user()?->hasRole('Администратор системы') || (int) $this->input('object_id') === (int) $this->user()?->objectId();
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'request_number' => ['required', 'string', 'max:255', 'unique:ndt_requests,request_number'],
            'request_date' => ['required', 'date'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'object_id' => ['required', 'integer', 'exists:objects,id'],
            'title_id' => ['nullable', 'integer', 'exists:titles,id'],
            'priority' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'basis' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
