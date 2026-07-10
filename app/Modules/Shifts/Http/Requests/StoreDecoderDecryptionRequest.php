<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDecoderDecryptionRequest extends FormRequest
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
            'result_text' => ['nullable', 'string'],
            'analysis_comment' => ['nullable', 'string'],
            'decrypted_at' => ['nullable', 'date'],
        ];
    }
}
