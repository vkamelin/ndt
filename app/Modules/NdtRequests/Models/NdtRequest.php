<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Models;

use App\Modules\Admin\Models\Title;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Welds\Models\Weld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class NdtRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'request_number',
        'request_date',
        'organization_id',
        'object_id',
        'title_id',
        'priority',
        'due_date',
        'basis',
        'comment',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'request_date' => 'date',
        'due_date' => 'date',
        'status' => NdtRequestStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }

    public function welds(): BelongsToMany
    {
        return $this->belongsToMany(Weld::class, 'ndt_request_items')->withTimestamps();
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(NdtRequestStatusHistory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(NdtTask::class, 'ndt_request_id');
    }
}
