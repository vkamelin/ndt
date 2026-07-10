<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Models;

use App\Modules\NdtResults\Models\NdtResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ConclusionItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'conclusion_id',
        'ndt_result_id',
        'sort_order',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function conclusion(): BelongsTo
    {
        return $this->belongsTo(Conclusion::class, 'conclusion_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(NdtResult::class, 'ndt_result_id');
    }
}
