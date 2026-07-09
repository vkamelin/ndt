<?php

declare(strict_types=1);

namespace App\Modules\Welds\Http\Requests;

use App\Modules\Welds\Enums\WeldStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateWeldStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(WeldStatus::class)],
            'comment' => ['nullable', 'string'],
        ];
    }
}
