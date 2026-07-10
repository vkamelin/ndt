<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SyncWeldNdtMethodsRequest extends FormRequest
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
            'method_ids' => ['required', 'array', 'min:1'],
            'method_ids.*' => ['integer', 'exists:ndt_methods,id'],
        ];
    }
}
