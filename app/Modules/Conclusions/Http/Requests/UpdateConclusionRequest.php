<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateConclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conclusion = $this->route('conclusion');

        return $conclusion !== null && $this->user()?->can('manage', $conclusion) === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'result_ids' => ['nullable', 'array', 'min:1'],
            'result_ids.*' => ['integer', 'distinct', 'exists:ndt_results,id'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
