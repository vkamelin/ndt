<?php

declare(strict_types=1);

namespace App\Modules\Registers\Http\Requests;

use App\Modules\Registers\Models\TransferRegister;
use Illuminate\Foundation\Http\FormRequest;

final class StoreTransferRegisterItemRequest extends FormRequest
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
            'related_type' => ['required', 'string', 'max:255'],
            'related_id' => ['required', 'integer'],
            'file_id' => ['nullable', 'integer', 'exists:files,id'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
