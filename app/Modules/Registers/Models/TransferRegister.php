<?php

declare(strict_types=1);

namespace App\Modules\Registers\Models;

use App\Modules\Admin\Models\RegisterType;
use App\Modules\Documents\Models\File;
use App\Modules\Employees\Models\Employee;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Registers\Enums\TransferRegisterStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class TransferRegister extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'register_type_id',
        'number',
        'date',
        'city_id',
        'object_id',
        'sender_employee_id',
        'receiver_employee_id',
        'status',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'status' => TransferRegisterStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(RegisterType::class, 'register_type_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function senderEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'sender_employee_id');
    }

    public function receiverEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'receiver_employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferRegisterItem::class, 'transfer_register_id');
    }

    public function acts(): HasMany
    {
        return $this->hasMany(Act::class, 'transfer_register_id');
    }

    public function archiveCases(): HasMany
    {
        return $this->hasMany(ArchiveCase::class, 'transfer_register_id');
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'related');
    }
}
