<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Radiography\Models\RtFilm;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class FilmInventoryTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shift_id',
        'rt_film_id',
        'operation',
        'quantity',
        'transacted_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'transacted_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(RtFilm::class, 'rt_film_id');
    }
}
