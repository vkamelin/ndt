<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Models;

use App\Modules\Employees\Models\Employee;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Models\Weld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class NdtTask extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'task_number',
        'ndt_request_id',
        'object_id',
        'ndt_method_id',
        'assignee_employee_id',
        'planned_date',
        'priority',
        'comment',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'planned_date' => 'date',
        'status' => NdtTaskStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(NdtRequest::class, 'ndt_request_id');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(NdtMethod::class, 'ndt_method_id');
    }

    public function assigneeEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assignee_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(NdtTaskItem::class, 'ndt_task_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(NdtTaskStatusHistory::class, 'ndt_task_id');
    }

    public function welds(): BelongsToMany
    {
        return $this->belongsToMany(Weld::class, 'ndt_task_items')
            ->withPivot('position_number')
            ->withTimestamps()
            ->orderBy('ndt_task_items.position_number');
    }
}
