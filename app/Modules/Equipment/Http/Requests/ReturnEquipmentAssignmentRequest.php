<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReturnEquipmentAssignmentRequest extends FormRequest
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
            'returned_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
