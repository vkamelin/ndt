<?php

declare(strict_types=1);

namespace App\Modules\Objects\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreObjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
