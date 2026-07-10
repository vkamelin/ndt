<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IssueConclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conclusion = $this->route('conclusion');

        return $conclusion !== null && $this->user()?->can('issue', $conclusion) === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'basis' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
