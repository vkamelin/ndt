<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Models;

use App\Models\User;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EquipmentMovement extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'from_object_id',
        'to_object_id',
        'recorded_by_user_id',
        'moved_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'moved_at' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function fromObject(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'from_object_id');
    }

    public function toObject(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'to_object_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
