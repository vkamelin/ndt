<?php

declare(strict_types=1);

namespace App\Modules\Registers\Http\Requests;

use App\Modules\Registers\Models\ArchiveCase;
use Illuminate\Foundation\Http\FormRequest;

final class StoreArchiveCaseItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ArchiveCase|null $archiveCase */
        $archiveCase = $this->route('archiveCase');

        return $archiveCase !== null
            && (
                $this->user()?->hasRole('Администратор системы') === true
                || $this->user()?->can('manage', $archiveCase) === true
            );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'related_type' => ['required', 'string', 'max:255'],
            'related_id' => ['required', 'integer'],
            'file_id' => ['nullable', 'integer', 'exists:files,id'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
