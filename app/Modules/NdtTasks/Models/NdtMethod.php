<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Models;

use App\Modules\NdtTasks\Enums\NdtMethodCode;
use App\Modules\Welds\Models\Weld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class NdtMethod extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'code' => NdtMethodCode::class,
        'is_active' => 'bool',
    ];

    public function welds(): BelongsToMany
    {
        return $this->belongsToMany(Weld::class, 'weld_ndt_methods')->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(NdtTask::class, 'ndt_method_id');
    }
}
