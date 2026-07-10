<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreConclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('conclusions.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'result_ids' => ['required', 'array', 'min:1'],
            'result_ids.*' => ['integer', 'distinct', 'exists:ndt_results,id'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
