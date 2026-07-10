<?php

declare(strict_types=1);

namespace App\Modules\Registers\Http\Requests;

use App\Modules\Registers\Models\TransferRegister;
use Illuminate\Foundation\Http\FormRequest;

final class StoreArchiveCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TransferRegister|null $register */
        $register = $this->route('transferRegister');

        return $register !== null && $this->user()?->can('archive', $register) === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'object_id' => ['required', 'integer', 'exists:objects,id'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
