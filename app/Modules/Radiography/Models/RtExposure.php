<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RtExposure extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rt_result_id',
        'rt_film_id',
        'exposure_number',
        'exposed_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'exposure_number' => 'integer',
        'exposed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(RtFilm::class, 'rt_film_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(RtResult::class, 'rt_result_id');
    }
}
