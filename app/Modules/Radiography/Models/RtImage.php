<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Models;

use App\Modules\Documents\Models\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RtImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rt_film_id',
        'file_id',
        'sequence_number',
        'captured_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sequence_number' => 'integer',
        'captured_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function film(): BelongsTo
    {
        return $this->belongsTo(RtFilm::class, 'rt_film_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
