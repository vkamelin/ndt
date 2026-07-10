<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EquipmentRepair extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'recorded_by_user_id',
        'started_at',
        'completed_at',
        'description',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
