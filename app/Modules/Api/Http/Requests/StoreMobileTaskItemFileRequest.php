<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreMobileTaskItemFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480'],
        ];
    }
}
