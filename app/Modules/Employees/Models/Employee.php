<?php

declare(strict_types=1);

namespace App\Modules\Employees\Models;

use App\Models\User;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'object_id',
        'position_id',
        'last_name',
        'first_name',
        'middle_name',
        'phone',
        'email',
        'status',
        'personnel_number',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => EmployeeStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function object(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Objects\Models\NdtObject::class, 'object_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(EmployeeQualification::class, 'employee_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_user')
            ->withTimestamps();
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related');
    }

    public function fullName(): string
    {
        return trim(implode(' ', array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name,
        ])));
    }
}
