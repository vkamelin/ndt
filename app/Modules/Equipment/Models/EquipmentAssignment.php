<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Models;

use App\Models\User;
use App\Modules\Employees\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EquipmentAssignment extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'employee_id',
        'recorded_by_user_id',
        'issued_at',
        'returned_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'issued_at' => 'date',
        'returned_at' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
