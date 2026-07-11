<?php

declare(strict_types=1);

namespace App\Modules\Registers\Http\Requests;

use App\Modules\Registers\Enums\TransferRegisterStatus;
use App\Modules\Registers\Models\TransferRegister;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTransferRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TransferRegister|null $register */
        $register = $this->route('transferRegister');

        return $register !== null && $this->user()?->can('manage', $register) === true;
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
