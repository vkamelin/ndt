<?php

declare(strict_types=1);

namespace App\Modules\Welds\Http\Requests;

use App\Modules\Welds\Models\Weld;
use Illuminate\Foundation\Http\FormRequest;

final class StoreWeldRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! ($this->user()?->can('welds.manage') ?? false)) {
            return false;
        }

        return $this->user()?->hasRole('Администратор системы') || (int) $this->input('object_id') === (int) $this->user()?->objectId();
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return [
            'object_id' => ['required', 'integer', 'exists:objects,id'],
            'title_id' => ['nullable', 'integer', 'exists:titles,id'],
            'drawing_id' => ['nullable', 'integer', 'exists:drawings,id'],
            'line_id' => ['nullable', 'integer', 'exists:lines,id'],
            'weld_number' => ['required', 'string', 'max:255'],
            'diameter' => ['nullable', 'numeric'],
            'thickness' => ['nullable', 'numeric'],
            'material_1_id' => ['nullable', 'integer', 'exists:materials,id'],
            'material_2_id' => ['nullable', 'integer', 'exists:materials,id'],
            'welded_at' => ['nullable', 'date'],
            'welding_process_id' => ['nullable', 'integer', 'exists:welding_processes,id'],
            'weld_type_id' => ['nullable', 'integer', 'exists:weld_types,id'],
            'pipeline_category_id' => ['nullable', 'integer', 'exists:pipeline_categories,id'],
            'medium_id' => ['nullable', 'integer', 'exists:media,id'],
            'pwht' => ['sometimes', 'boolean'],
            'normative_document_id' => ['nullable', 'integer', 'exists:normative_documents,id'],
        ];
    }
}
