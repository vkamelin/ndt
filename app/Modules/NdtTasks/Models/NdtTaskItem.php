<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Models;

use App\Modules\Documents\Models\File;
use App\Modules\Welds\Models\Weld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

final class NdtTaskItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_task_id',
        'weld_id',
        'position_number',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(NdtTask::class, 'ndt_task_id');
    }

    public function weld(): BelongsTo
    {
        return $this->belongsTo(Weld::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related');
    }
}
