<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Modules\Documents\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin File
 */
final class FileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'status' => $this->status->value,
            'related_type' => $this->related_type,
            'related_id' => $this->related_id,
            'download_url' => route('api.mobile.files.download', $this),
            'deleted_at' => $this->deleted_at?->toAtomString(),
        ];
    }
}
