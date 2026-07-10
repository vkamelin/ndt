<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Models;

use App\Modules\Employees\Models\Employee;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Enums\ShiftType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Shift extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'object_id',
        'type',
        'status',
        'started_at',
        'finished_at',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'type' => ShiftType::class,
        'status' => ShiftStatus::class,
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function labReport(): HasOne
    {
        return $this->hasOne(LabShiftReport::class, 'shift_id');
    }

    public function labRegulatoryWorks(): HasMany
    {
        return $this->hasMany(LabShiftRegulatoryWork::class, 'shift_id');
    }

    public function filmTransactions(): HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\FilmInventoryTransaction::class, 'shift_id');
    }

    public function chemicalTransactions(): HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\ChemicalInventoryTransaction::class, 'shift_id');
    }

    public function chemicalRequests(): HasMany
    {
        return $this->hasMany(\App\Modules\Inventory\Models\ChemicalRequest::class, 'shift_id');
    }

    public function decoderReport(): HasOne
    {
        return $this->hasOne(DecoderShiftReport::class, 'shift_id');
    }

    public function decoderFilmGroups(): HasMany
    {
        return $this->hasMany(DecoderFilmGroup::class, 'shift_id');
    }

    public function decoderRejects(): HasMany
    {
        return $this->hasMany(DecoderReject::class, 'shift_id');
    }

    public function decoderForgerySuspicion(): HasMany
    {
        return $this->hasMany(DecoderForgerySuspicion::class, 'shift_id');
    }

    public function decoderCleanups(): HasMany
    {
        return $this->hasMany(DecoderCleanup::class, 'shift_id');
    }

    public function decoderDecryptions(): HasMany
    {
        return $this->hasMany(DecoderDecryption::class, 'shift_id');
    }
}
