<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentAssignmentRequest extends FormRequest
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
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'issued_at' => ['required', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
