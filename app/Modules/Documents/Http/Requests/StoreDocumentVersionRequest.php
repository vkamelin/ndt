<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDocumentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');

        return $document !== null && $this->user()?->can('manage', $document) === true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480'],
            'basis' => ['required', 'string', 'max:1000'],
        ];
    }
}
