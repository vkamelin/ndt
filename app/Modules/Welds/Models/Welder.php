<?php

declare(strict_types=1);

namespace App\Modules\Welds\Models;

use App\Modules\Employees\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Welder extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'stamp',
        'is_active',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'bool',
        'deleted_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function welds(): BelongsToMany
    {
        return $this->belongsToMany(Weld::class, 'weld_welders')->withTimestamps();
    }

    public function displayName(): string
    {
        return $this->name ?: ($this->employee?->fullName() ?? 'Без имени');
    }
}
