<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Models;

use App\Modules\Documents\Models\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class RtArchiveItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rt_result_id',
        'rt_film_id',
        'file_id',
        'register_number',
        'archive_location',
        'archived_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'archived_at' => 'datetime',
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

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
