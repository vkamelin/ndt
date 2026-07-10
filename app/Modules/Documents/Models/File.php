<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Models\User;
use App\Modules\Documents\Enums\FileStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class File extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'original_name',
        'storage_name',
        'storage_path',
        'disk',
        'mime_type',
        'size',
        'hash',
        'uploaded_by_user_id',
        'related_type',
        'related_id',
        'status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'status' => FileStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_files')
            ->withTimestamps();
    }

    public function documentFiles(): HasMany
    {
        return $this->hasMany(DocumentFile::class, 'file_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'file_id');
    }
}
