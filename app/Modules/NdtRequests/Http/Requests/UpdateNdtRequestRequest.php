<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Requests;

use App\Modules\NdtRequests\Models\NdtRequest;
use Illuminate\Validation\Rule;

class UpdateNdtRequestRequest extends StoreNdtRequestRequest
{
    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        /** @var NdtRequest|null $ndtRequest */
        $ndtRequest = $this->route('ndtRequest');

        return [
            'request_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ndt_requests', 'request_number')->ignore($ndtRequest?->getKey()),
            ],
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
