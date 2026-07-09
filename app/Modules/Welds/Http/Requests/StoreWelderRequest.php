<?php

declare(strict_types=1);

namespace App\Modules\Welds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreWelderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('welders.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'name' => ['required', 'string', 'max:255'],
            'stamp' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
