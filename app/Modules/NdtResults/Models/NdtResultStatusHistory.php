<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NdtResultStatusHistory extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'ndt_result_status_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_result_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'comment',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(NdtResult::class, 'ndt_result_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
