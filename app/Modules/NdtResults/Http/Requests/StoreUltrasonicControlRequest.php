<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreUltrasonicControlRequest extends FormRequest
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
            'conclusion_number' => ['nullable', 'string', 'max:255'],
            'conclusion_date' => ['nullable', 'date'],
            'sounding_scheme' => ['nullable', 'string', 'max:255'],
            'transducer' => ['nullable', 'string', 'max:255'],
            'tuning_parameters' => ['nullable', 'string'],
            'transfer_register_number' => ['nullable', 'string', 'max:255'],
            'act_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
