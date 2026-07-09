<?php

declare(strict_types=1);

namespace App\Modules\Welds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreWeldWelderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('welds.manage') ?? false;
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'welder_id' => ['required', 'integer', 'exists:welders,id'],
        ];
    }
}
