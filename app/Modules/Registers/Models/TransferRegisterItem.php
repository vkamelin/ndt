<?php

declare(strict_types=1);

namespace App\Modules\Registers\Models;

use App\Modules\Documents\Models\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class TransferRegisterItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'transfer_register_id',
        'related_type',
        'related_id',
        'file_id',
        'sort_order',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function register(): BelongsTo
    {
        return $this->belongsTo(TransferRegister::class, 'transfer_register_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
