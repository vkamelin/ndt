<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ConclusionStatusHistory extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'conclusion_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'comment',
    ];

    public function conclusion(): BelongsTo
    {
        return $this->belongsTo(Conclusion::class, 'conclusion_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
