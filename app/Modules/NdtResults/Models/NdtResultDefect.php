<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Models;

use App\Modules\Admin\Models\DefectType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class NdtResultDefect extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_result_id',
        'defect_type_id',
        'description',
        'comment',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(NdtResult::class, 'ndt_result_id');
    }

    public function defectType(): BelongsTo
    {
        return $this->belongsTo(DefectType::class, 'defect_type_id');
    }
}
