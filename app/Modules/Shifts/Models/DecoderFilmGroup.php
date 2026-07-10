<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Models;

use App\Modules\Radiography\Models\RtResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class DecoderFilmGroup extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shift_id',
        'rt_result_id',
        'group_name',
        'viewed_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'viewed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(RtResult::class, 'rt_result_id');
    }
}
