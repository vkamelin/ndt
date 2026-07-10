<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class VtResult extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ndt_result_id',
        'conclusion_number',
        'conclusion_date',
        'measurements',
        'transfer_register_number',
        'act_number',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'conclusion_date' => 'date',
    ];

    public function result(): BelongsTo
    {
        return $this->belongsTo(NdtResult::class, 'ndt_result_id');
    }
}
