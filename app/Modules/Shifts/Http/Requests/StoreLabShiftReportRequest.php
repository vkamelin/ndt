<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreLabShiftReportRequest extends FormRequest
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
            'summary' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
            'completed_at' => ['nullable', 'date'],
        ];
    }
}
