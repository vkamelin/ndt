<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReplaceConclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conclusion = $this->route('conclusion');

        return $conclusion !== null && $this->user()?->can('replace', $conclusion) === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
