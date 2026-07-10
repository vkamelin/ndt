<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Models;

use App\Modules\Conclusions\Enums\ConclusionStatus;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Models\Employee;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Conclusion extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'date',
        'object_id',
        'ndt_method_id',
        'ndt_request_id',
        'prepared_by_employee_id',
        'checked_by_employee_id',
        'approved_by_employee_id',
        'status',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'status' => ConclusionStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(NdtMethod::class, 'ndt_method_id');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(NdtRequest::class, 'ndt_request_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'prepared_by_employee_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'checked_by_employee_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ConclusionItem::class, 'conclusion_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ConclusionVersion::class, 'conclusion_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(ConclusionStatusHistory::class, 'conclusion_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'conclusion_files')
            ->withPivot('attached_by_user_id')
            ->withTimestamps();
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ConclusionVersion::class, 'conclusion_id')->latestOfMany('version_number');
    }
}
