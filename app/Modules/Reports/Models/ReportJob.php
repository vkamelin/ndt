<?php

declare(strict_types=1);

namespace App\Modules\Reports\Models;

use App\Models\User;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Documents\Models\File;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Reports\Enums\ReportFormat;
use App\Modules\Reports\Enums\ReportStatus;
use App\Modules\Reports\Enums\ReportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReportJob extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'report_type',
        'format',
        'title',
        'entity_type',
        'entity_id',
        'city_id',
        'object_id',
        'requested_by_user_id',
        'filters',
        'status',
        'queued_at',
        'started_at',
        'finished_at',
        'error_message',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'report_type' => ReportType::class,
        'format' => ReportFormat::class,
        'filters' => 'array',
        'status' => ReportStatus::class,
        'queued_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function file(): MorphOne
    {
        return $this->morphOne(File::class, 'related');
    }
}
