<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDecoderFilmGroupRequest extends FormRequest
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
            'rt_result_id' => ['nullable', 'integer', 'exists:rt_results,id'],
            'group_name' => ['required', 'string', 'max:255'],
            'viewed_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
