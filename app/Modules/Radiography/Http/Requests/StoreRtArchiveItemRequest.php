<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRtArchiveItemRequest extends FormRequest
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
            'file_id' => ['nullable', 'integer', 'exists:files,id'],
            'register_number' => ['nullable', 'string', 'max:255'],
            'archive_location' => ['nullable', 'string', 'max:255'],
            'archived_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
