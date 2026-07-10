<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFilmInventoryTransactionRequest extends FormRequest
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
            'rt_film_id' => ['nullable', 'integer', 'exists:rt_films,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'transacted_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
