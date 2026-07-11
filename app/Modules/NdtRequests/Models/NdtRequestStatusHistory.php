<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NdtRequestStatusHistory extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'ndt_request_status_history';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_request_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'comment',
    ];

    public function ndtRequest(): BelongsTo
    {
        return $this->belongsTo(NdtRequest::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
