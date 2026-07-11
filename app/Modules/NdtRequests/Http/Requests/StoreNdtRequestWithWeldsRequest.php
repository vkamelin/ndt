<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Requests;

final class StoreNdtRequestWithWeldsRequest extends StoreNdtRequestRequest
{
    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'priority' => ['nullable', 'string', 'max:255'],
            'welds' => ['required', 'array', 'min:1'],
            'welds.*.weld_number' => ['required', 'string', 'max:255'],
            'welds.*.title_id' => ['nullable', 'integer', 'exists:titles,id'],
            'welds.*.drawing_id' => ['nullable', 'integer', 'exists:drawings,id'],
            'welds.*.line_id' => ['nullable', 'integer', 'exists:lines,id'],
            'welds.*.diameter' => ['nullable', 'numeric'],
            'welds.*.thickness' => ['nullable', 'numeric'],
            'welds.*.material_1_id' => ['nullable', 'integer', 'exists:materials,id'],
            'welds.*.material_2_id' => ['nullable', 'integer', 'exists:materials,id'],
            'welds.*.welded_at' => ['nullable', 'date'],
            'welds.*.welding_process_id' => ['nullable', 'integer', 'exists:welding_processes,id'],
            'welds.*.weld_type_id' => ['nullable', 'integer', 'exists:weld_types,id'],
            'welds.*.pipeline_category_id' => ['nullable', 'integer', 'exists:pipeline_categories,id'],
            'welds.*.medium_id' => ['nullable', 'integer', 'exists:media,id'],
            'welds.*.pwht' => ['nullable', 'boolean'],
            'welds.*.normative_document_id' => ['nullable', 'integer', 'exists:normative_documents,id'],
        ]);
    }
}
