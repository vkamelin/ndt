<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreNdtResultDefectRequest extends FormRequest
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
            'defect_type_id' => ['nullable', 'integer', 'exists:defect_types,id'],
            'description' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
