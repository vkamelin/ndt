<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreNdtRequestItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ndt_requests.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'weld_id' => ['required', 'integer', 'exists:welds,id'],
        ];
    }
}
