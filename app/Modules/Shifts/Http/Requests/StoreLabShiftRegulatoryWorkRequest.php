<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLabShiftRegulatoryWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('shifts.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'worked_at' => ['nullable', 'date'],
            'description' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
