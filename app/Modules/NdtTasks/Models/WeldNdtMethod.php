<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Models;

use App\Modules\Welds\Models\Weld;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WeldNdtMethod extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'weld_id',
        'ndt_method_id',
    ];

    public function weld(): BelongsTo
    {
        return $this->belongsTo(Weld::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(NdtMethod::class, 'ndt_method_id');
    }
}
