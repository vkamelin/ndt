<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentRequest extends FormRequest
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
            'equipment_type_id' => ['required', 'integer', 'exists:equipment_types,id'],
            'object_id' => ['required', 'integer', 'exists:objects,id'],
            'name' => ['required', 'string', 'max:255'],
            'inventory_number' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:available,issued,in_repair,defective,written_off'],
            'purchased_at' => ['nullable', 'date'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
