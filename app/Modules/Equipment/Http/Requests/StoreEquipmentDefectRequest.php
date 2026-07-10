<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentDefectRequest extends FormRequest
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
            'detected_at' => ['required', 'date'],
            'description' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
