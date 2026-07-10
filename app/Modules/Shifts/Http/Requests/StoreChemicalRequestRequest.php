<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreChemicalRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('shifts.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'chemical_type_id' => ['nullable', 'integer', 'exists:chemical_types,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'requested_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
