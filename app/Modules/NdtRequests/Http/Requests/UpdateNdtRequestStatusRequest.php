<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Requests;

use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateNdtRequestStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(NdtRequestStatus::class)],
            'comment' => ['nullable', 'string'],
        ];
    }
}
