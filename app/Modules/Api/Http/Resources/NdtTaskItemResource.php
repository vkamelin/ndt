<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Modules\NdtTasks\Models\NdtTaskItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NdtTaskItem
 */
final class NdtTaskItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'position_number' => $this->position_number,
            'weld' => $this->whenLoaded('weld', fn () => new WeldResource($this->weld)),
            'files' => $this->whenLoaded('files', fn (): array => $this->files->map(static fn ($file): FileResource => new FileResource($file))->values()->all(), []),
        ];
    }
}
