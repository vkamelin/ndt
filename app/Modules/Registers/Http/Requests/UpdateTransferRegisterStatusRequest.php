<?php

declare(strict_types=1);

namespace App\Modules\Registers\Http\Requests;

use App\Modules\Registers\Enums\TransferRegisterStatus;
use App\Modules\Registers\Models\TransferRegister;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTransferRegisterStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TransferRegister|null $register */
        $register = $this->route('transferRegister');

        return $register !== null && $this->user()?->can('transition', $register) === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TransferRegisterStatus::class)],
            'comment' => ['nullable', 'string'],
        ];
    }
}
