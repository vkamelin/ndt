<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Models;

use App\Modules\Admin\Models\FilmType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RtFilm extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rt_result_id',
        'film_type_id',
        'barcode',
        'position_number',
        'exposure_count',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'position_number' => 'integer',
        'exposure_count' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(RtResult::class, 'rt_result_id');
    }

    public function filmType(): BelongsTo
    {
        return $this->belongsTo(FilmType::class, 'film_type_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(RtImage::class, 'rt_film_id');
    }

    public function exposures(): HasMany
    {
        return $this->hasMany(RtExposure::class, 'rt_film_id');
    }
}
