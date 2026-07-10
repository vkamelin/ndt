<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Models;

use App\Modules\Admin\Models\FilmType;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Models\Employee;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\Radiography\Enums\RtStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RtResult extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_result_id',
        'film_type_id',
        'barcode',
        'conclusion_number',
        'control_date',
        'conclusion_date',
        'archive_location',
        'result_text',
        'comment',
        'reshoot_reason',
        'status',
        'decoded_at',
        'sent_to_analysis_at',
        'included_in_conclusion_at',
        'archived_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'control_date' => 'date',
        'conclusion_date' => 'date',
        'decoded_at' => 'datetime',
        'sent_to_analysis_at' => 'datetime',
        'included_in_conclusion_at' => 'datetime',
        'archived_at' => 'datetime',
        'status' => RtStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function ndtResult(): BelongsTo
    {
        return $this->belongsTo(NdtResult::class, 'ndt_result_id');
    }

    public function filmType(): BelongsTo
    {
        return $this->belongsTo(FilmType::class, 'film_type_id');
    }

    public function films(): HasMany
    {
        return $this->hasMany(RtFilm::class, 'rt_result_id');
    }

    public function exposures(): HasMany
    {
        return $this->hasMany(RtExposure::class, 'rt_result_id');
    }

    public function reshoots(): HasMany
    {
        return $this->hasMany(RtReshoot::class, 'rt_result_id');
    }

    public function densityMeasurements(): HasMany
    {
        return $this->hasMany(RtDensityMeasurement::class, 'rt_result_id');
    }

    public function archiveItems(): HasMany
    {
        return $this->hasMany(RtArchiveItem::class, 'rt_result_id');
    }

    public function latestArchiveItem(): HasOne
    {
        return $this->hasOne(RtArchiveItem::class, 'rt_result_id')->latestOfMany('archived_at');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related');
    }
}
