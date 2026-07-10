<?php

declare(strict_types=1);

namespace App\Modules\Registers\Models;

use App\Modules\Admin\Models\ActType;
use App\Modules\Documents\Models\File;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Act extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'act_type_id',
        'transfer_register_id',
        'number',
        'date',
        'city_id',
        'object_id',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ActType::class, 'act_type_id');
    }

    public function register(): BelongsTo
    {
        return $this->belongsTo(TransferRegister::class, 'transfer_register_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related');
    }
}
