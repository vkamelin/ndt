<?php

declare(strict_types=1);

namespace App\Modules\Audit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AuditLog extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'actor_user_id',
        'subject_type',
        'subject_id',
        'event',
        'properties',
        'reason',
        'ip_address',
        'user_agent',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
