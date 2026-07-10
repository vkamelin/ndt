<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EquipmentDocument extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'recorded_by_user_id',
        'document_name',
        'document_number',
        'issued_at',
        'valid_until',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'issued_at' => 'date',
        'valid_until' => 'date',
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
