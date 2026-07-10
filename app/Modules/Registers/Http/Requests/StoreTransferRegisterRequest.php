<?php

declare(strict_types=1);

namespace App\Modules\Registers\Http\Requests;

use App\Modules\Registers\Enums\TransferRegisterStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTransferRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('registers.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'register_type_id' => ['required', 'integer', 'exists:register_types,id'],
            'number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'object_id' => ['required', 'integer', 'exists:objects,id'],
            'sender_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'receiver_employee_id' => ['required', 'integer', 'exists:employees,id'],
            'status' => ['required', Rule::enum(TransferRegisterStatus::class)],
            'comment' => ['nullable', 'string'],
        ];
    }
}
