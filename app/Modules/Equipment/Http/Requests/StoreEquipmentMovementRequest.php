<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentMovementRequest extends FormRequest
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
            'from_object_id' => ['nullable', 'integer', 'exists:objects,id'],
            'to_object_id' => ['required', 'integer', 'exists:objects,id'],
            'moved_at' => ['required', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
