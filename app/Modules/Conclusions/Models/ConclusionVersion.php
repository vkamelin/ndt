<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Models;

use App\Models\User;
use App\Modules\Conclusions\Enums\ConclusionVersionStatus;
use App\Modules\Documents\Models\File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ConclusionVersion extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'conclusion_id',
        'version_number',
        'file_id',
        'created_by_user_id',
        'basis',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'version_number' => 'integer',
        'status' => ConclusionVersionStatus::class,
    ];

    public function conclusion(): BelongsTo
    {
        return $this->belongsTo(Conclusion::class, 'conclusion_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'file_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
