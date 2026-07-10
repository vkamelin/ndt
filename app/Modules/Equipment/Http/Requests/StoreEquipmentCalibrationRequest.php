<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentCalibrationRequest extends FormRequest
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
            'calibrated_at' => ['required', 'date'],
            'valid_until' => ['nullable', 'date'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
