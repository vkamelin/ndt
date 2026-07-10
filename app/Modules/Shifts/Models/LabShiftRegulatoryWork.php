<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class LabShiftRegulatoryWork extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shift_id',
        'worked_at',
        'description',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'worked_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
