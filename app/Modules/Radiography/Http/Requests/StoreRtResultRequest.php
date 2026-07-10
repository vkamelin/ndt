<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRtResultRequest extends FormRequest
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
            'ndt_result_id' => ['required', 'integer', 'exists:ndt_results,id'],
            'film_type_id' => ['nullable', 'integer', 'exists:film_types,id'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'conclusion_number' => ['nullable', 'string', 'max:255'],
            'control_date' => ['nullable', 'date'],
            'conclusion_date' => ['nullable', 'date'],
            'archive_location' => ['nullable', 'string', 'max:255'],
            'result_text' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
