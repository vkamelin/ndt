<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateRtStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('radiography.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
