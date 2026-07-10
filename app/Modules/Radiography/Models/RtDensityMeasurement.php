<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RtDensityMeasurement extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rt_result_id',
        'rt_film_id',
        'density',
        'minimum_density',
        'maximum_density',
        'measured_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'density' => 'decimal:3',
        'minimum_density' => 'decimal:3',
        'maximum_density' => 'decimal:3',
        'measured_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(RtResult::class, 'rt_result_id');
    }

    public function film(): BelongsTo
    {
        return $this->belongsTo(RtFilm::class, 'rt_film_id');
    }
}
