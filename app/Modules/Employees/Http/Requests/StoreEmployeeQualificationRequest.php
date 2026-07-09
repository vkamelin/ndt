<?php

declare(strict_types=1);

namespace App\Modules\Employees\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEmployeeQualificationRequest extends FormRequest
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
            'method' => ['required', 'string', 'in:rk,vik,pvk,mk,uk'],
            'valid_until' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
