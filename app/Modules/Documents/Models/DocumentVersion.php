<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Models\User;
use App\Modules\Documents\Enums\DocumentVersionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DocumentVersion extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'document_id',
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
        'status' => DocumentVersionStatus::class,
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
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
