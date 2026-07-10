<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRtFilmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('radiography.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'film_type_id' => ['nullable', 'integer', 'exists:film_types,id'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'position_number' => ['nullable', 'integer', 'min:1'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
