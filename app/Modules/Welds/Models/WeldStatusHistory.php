<?php

declare(strict_types=1);

namespace App\Modules\Welds\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WeldStatusHistory extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'weld_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'comment',
    ];

    public function weld(): BelongsTo
    {
        return $this->belongsTo(Weld::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
