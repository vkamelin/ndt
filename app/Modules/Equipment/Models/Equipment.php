<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Models;

use App\Modules\Documents\Models\File;
use App\Modules\Equipment\Enums\EquipmentStatus;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Equipment extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_type_id',
        'object_id',
        'name',
        'inventory_number',
        'serial_number',
        'manufacturer',
        'model',
        'status',
        'purchased_at',
        'write_off_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'status' => EquipmentStatus::class,
        'purchased_at' => 'date',
        'write_off_at' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(EquipmentType::class, 'equipment_type_id');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(EquipmentVerification::class, 'equipment_id');
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(EquipmentVerification::class, 'equipment_id')->latestOfMany('verified_at');
    }

    public function calibrations(): HasMany
    {
        return $this->hasMany(EquipmentCalibration::class, 'equipment_id');
    }

    public function latestCalibration(): HasOne
    {
        return $this->hasOne(EquipmentCalibration::class, 'equipment_id')->latestOfMany('calibrated_at');
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(EquipmentRepair::class, 'equipment_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EquipmentAssignment::class, 'equipment_id');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(EquipmentAssignment::class, 'equipment_id')->whereNull('returned_at')->latestOfMany('issued_at');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(EquipmentMovement::class, 'equipment_id');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(EquipmentDefect::class, 'equipment_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EquipmentDocument::class, 'equipment_id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related');
    }

    public function isUsable(): bool
    {
        return $this->status instanceof EquipmentStatus
            ? $this->status->isUsable()
            : EquipmentStatus::from($this->status)->isUsable();
    }
}
