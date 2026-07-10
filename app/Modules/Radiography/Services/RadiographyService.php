<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\Radiography\Enums\RtStatus;
use App\Modules\Radiography\Models\RtArchiveItem;
use App\Modules\Radiography\Models\RtDensityMeasurement;
use App\Modules\Radiography\Models\RtExposure;
use App\Modules\Radiography\Models\RtFilm;
use App\Modules\Radiography\Models\RtImage;
use App\Modules\Radiography\Models\RtReshoot;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class RadiographyService
{
    use RecordsAuditLogs;

    /**
     * Create or refresh the radiography card for a general NDT result.
     *
     * @param  array{
     *     film_type_id?: int|null,
     *     barcode?: string|null,
     *     conclusion_number?: string|null,
     *     control_date?: string|null,
     *     conclusion_date?: string|null,
     *     archive_location?: string|null,
     *     result_text?: string|null,
     *     comment?: string|null
     * }  $data
     */
    public function createOrUpdate(NdtResult $ndtResult, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return DB::transaction(function () use ($ndtResult, $data, $actor, $ipAddress, $userAgent): RtResult {
            $rtResult = RtResult::query()->updateOrCreate(
                ['ndt_result_id' => $ndtResult->getKey()],
                [
                    'film_type_id' => $data['film_type_id'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'conclusion_number' => $data['conclusion_number'] ?? null,
                    'control_date' => $data['control_date'] ?? $ndtResult->control_date?->toDateString(),
                    'conclusion_date' => $data['conclusion_date'] ?? null,
                    'archive_location' => $data['archive_location'] ?? null,
                    'result_text' => $data['result_text'] ?? null,
                    'comment' => $data['comment'] ?? null,
                    'status' => RtStatus::Assigned,
                ],
            );

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtResult::class,
                    entityId: $rtResult->getKey(),
                    operation: 'rt_result.saved',
                    after: $this->snapshot($rtResult->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $rtResult;
        });
    }

    /**
     * @param  array{
     *     film_type_id?: int|null,
     *     barcode?: string|null,
     *     position_number?: int|null,
     *     comment?: string|null
     * }  $data
     */
    public function addFilm(RtResult $result, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): RtFilm
    {
        return DB::transaction(function () use ($result, $data, $actor, $ipAddress, $userAgent): RtFilm {
            $film = $result->films()->create([
                'film_type_id' => $data['film_type_id'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'position_number' => $data['position_number'] ?? null,
                'comment' => $data['comment'] ?? null,
                'exposure_count' => 0,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtFilm::class,
                    entityId: $film->getKey(),
                    operation: 'rt_film.created',
                    after: $this->filmSnapshot($film->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $film;
        });
    }

    /**
     * @param  array{
     *     file_id?: int|null,
     *     sequence_number?: int|null,
     *     captured_at?: string|null,
     *     comment?: string|null
     * }  $data
     */
    public function addImage(RtFilm $film, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): RtImage
    {
        return DB::transaction(function () use ($film, $data, $actor, $ipAddress, $userAgent): RtImage {
            $image = $film->images()->create([
                'file_id' => $data['file_id'] ?? null,
                'sequence_number' => $data['sequence_number'] ?? 1,
                'captured_at' => $data['captured_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtImage::class,
                    entityId: $image->getKey(),
                    operation: 'rt_image.created',
                    after: $this->imageSnapshot($image->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $image;
        });
    }

    /**
     * @param  array{
     *     exposure_number?: int|null,
     *     exposed_at?: string|null,
     *     comment?: string|null
     * }  $data
     */
    public function addExposure(RtFilm $film, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): RtExposure
    {
        return DB::transaction(function () use ($film, $data, $actor, $ipAddress, $userAgent): RtExposure {
            $exposure = $film->exposures()->create([
                'rt_result_id' => $film->rt_result_id,
                'exposure_number' => $data['exposure_number'] ?? 1,
                'exposed_at' => $data['exposed_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $film->increment('exposure_count');
            $this->changeStatus($film->result()->firstOrFail()->refresh(), RtStatus::Shot, $actor, 'Снимок добавлен', $ipAddress, $userAgent);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtExposure::class,
                    entityId: $exposure->getKey(),
                    operation: 'rt_exposure.created',
                    after: $this->exposureSnapshot($exposure->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $exposure;
        });
    }

    /**
     * @param  array{
     *     rt_film_id?: int|null,
     *     reason: string,
     *     reshot_at?: string|null,
     *     comment?: string|null
     * }  $data
     */
    public function addReshoot(RtResult $result, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): RtReshoot
    {
        return DB::transaction(function () use ($result, $data, $actor, $ipAddress, $userAgent): RtReshoot {
            $reshoot = $result->reshoots()->create([
                'rt_film_id' => $data['rt_film_id'] ?? null,
                'reason' => $data['reason'],
                'reshot_at' => $data['reshot_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->changeStatus($result, RtStatus::NeedsReshoot, $actor, 'Фиксация пересвета', $ipAddress, $userAgent);
            app(NotificationService::class)->notifyReshootRequired($result);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtReshoot::class,
                    entityId: $reshoot->getKey(),
                    operation: 'rt_reshoot.created',
                    after: $this->reshootSnapshot($reshoot->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $reshoot;
        });
    }

    /**
     * @param  array{
     *     rt_film_id?: int|null,
     *     density?: numeric-string|float|int|null,
     *     minimum_density?: numeric-string|float|int|null,
     *     maximum_density?: numeric-string|float|int|null,
     *     measured_at?: string|null,
     *     comment?: string|null
     * }  $data
     */
    public function addDensityMeasurement(RtResult $result, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): RtDensityMeasurement
    {
        return DB::transaction(function () use ($result, $data, $actor, $ipAddress, $userAgent): RtDensityMeasurement {
            $measurement = $result->densityMeasurements()->create([
                'rt_film_id' => $data['rt_film_id'] ?? null,
                'density' => $data['density'] ?? null,
                'minimum_density' => $data['minimum_density'] ?? null,
                'maximum_density' => $data['maximum_density'] ?? null,
                'measured_at' => $data['measured_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtDensityMeasurement::class,
                    entityId: $measurement->getKey(),
                    operation: 'rt_density.created',
                    after: $this->densitySnapshot($measurement->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $measurement;
        });
    }

    /**
     * @param  array{
     *     rt_film_id?: int|null,
     *     file_id?: int|null,
     *     register_number?: string|null,
     *     archive_location?: string|null,
     *     archived_at?: string|null,
     *     comment?: string|null
     * }  $data
     */
    public function addArchiveItem(RtResult $result, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): RtArchiveItem
    {
        return DB::transaction(function () use ($result, $data, $actor, $ipAddress, $userAgent): RtArchiveItem {
            $archiveItem = $result->archiveItems()->create([
                'rt_film_id' => $data['rt_film_id'] ?? null,
                'file_id' => $data['file_id'] ?? null,
                'register_number' => $data['register_number'] ?? null,
                'archive_location' => $data['archive_location'] ?? null,
                'archived_at' => $data['archived_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $result->fill([
                'archive_location' => $data['archive_location'] ?? $result->archive_location,
                'archived_at' => $data['archived_at'] ?? now(),
            ])->save();

            $this->transition($result->refresh(), RtStatus::Archived);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtArchiveItem::class,
                    entityId: $archiveItem->getKey(),
                    operation: 'rt_archive_item.created',
                    after: $this->archiveSnapshot($archiveItem->refresh()),
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $archiveItem;
        });
    }

    public function transferToLab(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return $this->transitionWithAudit($result, RtStatus::LabTransferred, 'rt_result.transferred_to_lab', $actor, $comment, $ipAddress, $userAgent);
    }

    public function markProcessing(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return $this->transitionWithAudit($result, RtStatus::Processing, 'rt_result.processing', $actor, $comment, $ipAddress, $userAgent);
    }

    public function markReadyForDecoding(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return $this->transitionWithAudit($result, RtStatus::ReadyForDecoding, 'rt_result.ready_for_decoding', $actor, $comment, $ipAddress, $userAgent);
    }

    public function startDecoding(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return $this->transitionWithAudit($result, RtStatus::Decoding, 'rt_result.decoding_started', $actor, $comment, $ipAddress, $userAgent);
    }

    public function markReshootDone(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return $this->transitionWithAudit($result, RtStatus::ReshootDone, 'rt_result.reshoot_done', $actor, $comment, $ipAddress, $userAgent);
    }

    public function markDecoded(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return DB::transaction(function () use ($result, $actor, $comment, $ipAddress, $userAgent): RtResult {
            $before = $this->snapshot($result);
            $result->fill([
                'decoded_at' => now(),
                'status' => RtStatus::Decoded,
            ])->save();
            $result->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtResult::class,
                    entityId: $result->getKey(),
                    operation: 'rt_result.decoded',
                    before: $before,
                    after: $this->snapshot($result),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $result;
        });
    }

    public function sendToAnalysis(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return DB::transaction(function () use ($result, $actor, $comment, $ipAddress, $userAgent): RtResult {
            $before = $this->snapshot($result);
            $result->fill([
                'sent_to_analysis_at' => now(),
                'status' => RtStatus::SentToAnalysis,
            ])->save();
            $result->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtResult::class,
                    entityId: $result->getKey(),
                    operation: 'rt_result.sent_to_analysis',
                    before: $before,
                    after: $this->snapshot($result),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $result;
        });
    }

    public function includeInConclusion(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return DB::transaction(function () use ($result, $actor, $comment, $ipAddress, $userAgent): RtResult {
            $before = $this->snapshot($result);
            $result->fill([
                'included_in_conclusion_at' => now(),
                'status' => RtStatus::IncludedInConclusion,
            ])->save();
            $result->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtResult::class,
                    entityId: $result->getKey(),
                    operation: 'rt_result.included_in_conclusion',
                    before: $before,
                    after: $this->snapshot($result),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $result;
        });
    }

    public function archive(RtResult $result, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return DB::transaction(function () use ($result, $actor, $comment, $ipAddress, $userAgent): RtResult {
            $before = $this->snapshot($result);
            $result->fill([
                'archived_at' => now(),
                'status' => RtStatus::Archived,
            ])->save();
            $result->refresh();

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtResult::class,
                    entityId: $result->getKey(),
                    operation: 'rt_result.archived',
                    before: $before,
                    after: $this->snapshot($result),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $result;
        });
    }

    public function changeStatus(RtResult $result, RtStatus $status, ?User $actor = null, ?string $comment = null, ?string $ipAddress = null, ?string $userAgent = null): RtResult
    {
        return $this->transitionWithAudit($result, $status, 'rt_result.status_updated', $actor, $comment, $ipAddress, $userAgent);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(RtResult $result): array
    {
        return [
            'id' => $result->getKey(),
            'ndt_result_id' => $result->ndt_result_id,
            'film_type_id' => $result->film_type_id,
            'barcode' => $result->barcode,
            'conclusion_number' => $result->conclusion_number,
            'status' => $result->status->value,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filmSnapshot(RtFilm $film): array
    {
        return [
            'id' => $film->getKey(),
            'rt_result_id' => $film->rt_result_id,
            'film_type_id' => $film->film_type_id,
            'barcode' => $film->barcode,
            'position_number' => $film->position_number,
            'exposure_count' => $film->exposure_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function imageSnapshot(RtImage $image): array
    {
        return [
            'id' => $image->getKey(),
            'rt_film_id' => $image->rt_film_id,
            'file_id' => $image->file_id,
            'sequence_number' => $image->sequence_number,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function exposureSnapshot(RtExposure $exposure): array
    {
        return [
            'id' => $exposure->getKey(),
            'rt_film_id' => $exposure->rt_film_id,
            'exposure_number' => $exposure->exposure_number,
            'exposed_at' => $exposure->exposed_at?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reshootSnapshot(RtReshoot $reshoot): array
    {
        return [
            'id' => $reshoot->getKey(),
            'rt_result_id' => $reshoot->rt_result_id,
            'rt_film_id' => $reshoot->rt_film_id,
            'reason' => $reshoot->reason,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function densitySnapshot(RtDensityMeasurement $measurement): array
    {
        return [
            'id' => $measurement->getKey(),
            'rt_result_id' => $measurement->rt_result_id,
            'rt_film_id' => $measurement->rt_film_id,
            'density' => $measurement->density,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function archiveSnapshot(RtArchiveItem $archiveItem): array
    {
        return [
            'id' => $archiveItem->getKey(),
            'rt_result_id' => $archiveItem->rt_result_id,
            'rt_film_id' => $archiveItem->rt_film_id,
            'file_id' => $archiveItem->file_id,
            'register_number' => $archiveItem->register_number,
            'archive_location' => $archiveItem->archive_location,
        ];
    }

    private function transitionWithAudit(
        RtResult $result,
        RtStatus $toStatus,
        string $operation,
        ?User $actor = null,
        ?string $comment = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): RtResult {
        return DB::transaction(function () use ($result, $toStatus, $operation, $actor, $comment, $ipAddress, $userAgent): RtResult {
            $before = $this->snapshot($result);
            $this->transition($result, $toStatus);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: RtResult::class,
                    entityId: $result->getKey(),
                    operation: $operation,
                    before: $before,
                    after: $this->snapshot($result->refresh()),
                    actor: $actor,
                    reason: $comment,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $result;
        });
    }

    private function transition(RtResult $result, RtStatus $toStatus): void
    {
        $result->fill(['status' => $toStatus])->save();
    }
}
