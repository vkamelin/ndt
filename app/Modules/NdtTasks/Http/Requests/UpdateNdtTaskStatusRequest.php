<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateNdtTaskStatusRequest extends FormRequest
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
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
