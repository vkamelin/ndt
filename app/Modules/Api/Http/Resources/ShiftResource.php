<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Resources;

use App\Modules\Shifts\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shift
 */
final class ShiftResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'object_id' => $this->object_id,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'started_at' => $this->started_at?->toAtomString(),
            'finished_at' => $this->finished_at?->toAtomString(),
            'comment' => $this->comment,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'name' => trim(implode(' ', array_filter([
                    $this->employee->last_name,
                    $this->employee->first_name,
                    $this->employee->middle_name,
                ]))),
                'object_id' => $this->employee->object_id,
            ]),
            'lab_report' => $this->whenLoaded('labReport', fn () => $this->labReport === null ? null : [
                'id' => $this->labReport->id,
                'summary' => $this->labReport->summary,
                'completed_at' => $this->labReport->completed_at?->toAtomString(),
            ]),
            'lab_regulatory_works' => $this->whenLoaded('labRegulatoryWorks', fn (): array => $this->labRegulatoryWorks->map(static fn ($work): array => [
                'id' => $work->id,
                'worked_at' => $work->worked_at?->toAtomString(),
                'description' => $work->description,
                'comment' => $work->comment,
            ])->values()->all(), []),
            'film_transactions' => $this->whenLoaded('filmTransactions', fn (): array => $this->filmTransactions->map(static fn ($transaction): array => [
                'id' => $transaction->id,
                'operation' => $transaction->operation,
                'quantity' => $transaction->quantity,
                'transacted_at' => $transaction->transacted_at?->toAtomString(),
                'comment' => $transaction->comment,
            ])->values()->all(), []),
            'chemical_transactions' => $this->whenLoaded('chemicalTransactions', fn (): array => $this->chemicalTransactions->map(static fn ($transaction): array => [
                'id' => $transaction->id,
                'operation' => $transaction->operation,
                'quantity' => $transaction->quantity,
                'transacted_at' => $transaction->transacted_at?->toAtomString(),
                'comment' => $transaction->comment,
            ])->values()->all(), []),
            'chemical_requests' => $this->whenLoaded('chemicalRequests', fn (): array => $this->chemicalRequests->map(static fn ($requestItem): array => [
                'id' => $requestItem->id,
                'quantity' => $requestItem->quantity,
                'requested_at' => $requestItem->requested_at?->toAtomString(),
                'comment' => $requestItem->comment,
            ])->values()->all(), []),
            'decoder_report' => $this->whenLoaded('decoderReport', fn () => $this->decoderReport === null ? null : [
                'id' => $this->decoderReport->id,
                'summary' => $this->decoderReport->summary,
                'completed_at' => $this->decoderReport->completed_at?->toAtomString(),
            ]),
            'decoder_film_groups' => $this->whenLoaded('decoderFilmGroups', fn (): array => $this->decoderFilmGroups->map(static fn ($group): array => [
                'id' => $group->id,
                'group_name' => $group->group_name,
                'viewed_at' => $group->viewed_at?->toAtomString(),
                'comment' => $group->comment,
            ])->values()->all(), []),
            'decoder_rejects' => $this->whenLoaded('decoderRejects', fn (): array => $this->decoderRejects->map(static fn ($reject): array => [
                'id' => $reject->id,
                'reason' => $reject->reason,
                'recorded_at' => $reject->recorded_at?->toAtomString(),
                'comment' => $reject->comment,
            ])->values()->all(), []),
            'decoder_forgery_suspicions' => $this->whenLoaded('decoderForgerySuspicion', fn (): array => $this->decoderForgerySuspicion->map(static fn ($suspicion): array => [
                'id' => $suspicion->id,
                'reason' => $suspicion->reason,
                'recorded_at' => $suspicion->recorded_at?->toAtomString(),
                'comment' => $suspicion->comment,
            ])->values()->all(), []),
            'decoder_cleanups' => $this->whenLoaded('decoderCleanups', fn (): array => $this->decoderCleanups->map(static fn ($cleanup): array => [
                'id' => $cleanup->id,
                'completed_at' => $cleanup->completed_at?->toAtomString(),
                'comment' => $cleanup->comment,
            ])->values()->all(), []),
            'decoder_decryptions' => $this->whenLoaded('decoderDecryptions', fn (): array => $this->decoderDecryptions->map(static fn ($decryption): array => [
                'id' => $decryption->id,
                'result_text' => $decryption->result_text,
                'analysis_comment' => $decryption->analysis_comment,
                'decrypted_at' => $decryption->decrypted_at?->toAtomString(),
            ])->values()->all(), []),
        ];
    }
}
