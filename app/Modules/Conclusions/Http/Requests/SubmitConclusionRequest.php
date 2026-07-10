<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubmitConclusionRequest extends FormRequest
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
            'comment' => ['nullable', 'string'],
        ];
    }
}
