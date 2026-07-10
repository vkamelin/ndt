<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Admin\Models\ChemicalType;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ChemicalRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shift_id',
        'chemical_type_id',
        'quantity',
        'status',
        'requested_at',
        'closed_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'requested_at' => 'datetime',
        'closed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function chemicalType(): BelongsTo
    {
        return $this->belongsTo(ChemicalType::class, 'chemical_type_id');
    }
}
