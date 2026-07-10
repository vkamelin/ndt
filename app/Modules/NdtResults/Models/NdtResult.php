<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Models;

use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Models\Employee;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Welds\Models\Weld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class NdtResult extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_task_id',
        'weld_id',
        'ndt_method_id',
        'executor_employee_id',
        'equipment_id',
        'normative_document_id',
        'control_date',
        'analyzed_at',
        'result_text',
        'comment',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'control_date' => 'date',
        'analyzed_at' => 'datetime',
        'status' => NdtResultStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(NdtTask::class, 'ndt_task_id');
    }

    public function weld(): BelongsTo
    {
        return $this->belongsTo(Weld::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(NdtMethod::class, 'ndt_method_id');
    }

    public function executorEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'executor_employee_id');
    }

    public function normativeDocument(): BelongsTo
    {
        return $this->belongsTo(NormativeDocument::class, 'normative_document_id');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(NdtResultDefect::class, 'ndt_result_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(NdtResultStatusHistory::class, 'ndt_result_id');
    }

    public function vtResult(): HasOne
    {
        return $this->hasOne(VtResult::class, 'ndt_result_id');
    }

    public function ptResult(): HasOne
    {
        return $this->hasOne(PtResult::class, 'ndt_result_id');
    }

    public function mtResult(): HasOne
    {
        return $this->hasOne(MtResult::class, 'ndt_result_id');
    }

    public function utResult(): HasOne
    {
        return $this->hasOne(UtResult::class, 'ndt_result_id');
    }

    public function rtResult(): HasOne
    {
        return $this->hasOne(RtResult::class, 'ndt_result_id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related');
    }
}
