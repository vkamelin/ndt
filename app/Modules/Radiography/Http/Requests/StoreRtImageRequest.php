<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRtImageRequest extends FormRequest
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
            'file_id' => ['nullable', 'integer', 'exists:files,id'],
            'sequence_number' => ['nullable', 'integer', 'min:1'],
            'captured_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
