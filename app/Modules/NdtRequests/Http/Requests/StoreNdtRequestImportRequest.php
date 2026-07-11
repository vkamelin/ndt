<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Requests;

final class StoreNdtRequestImportRequest extends StoreNdtRequestRequest
{
    /**
     * @return array<string, list<string>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'priority' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:csv,xlsx'],
        ]);
    }
}
