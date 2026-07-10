<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRtDensityMeasurementRequest extends FormRequest
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
            'rt_film_id' => ['nullable', 'integer', 'exists:rt_films,id'],
            'density' => ['nullable', 'numeric'],
            'minimum_density' => ['nullable', 'numeric'],
            'maximum_density' => ['nullable', 'numeric'],
            'measured_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
