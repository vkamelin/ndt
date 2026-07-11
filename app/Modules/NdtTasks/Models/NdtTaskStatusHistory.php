<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NdtTaskStatusHistory extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'ndt_task_status_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_task_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'comment',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(NdtTask::class, 'ndt_task_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
