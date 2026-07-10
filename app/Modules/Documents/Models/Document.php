<?php

declare(strict_types=1);

namespace App\Modules\Documents\Models;

use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Document extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'document_type_id',
        'number',
        'document_date',
        'organization_id',
        'city_id',
        'object_id',
        'employee_id',
        'equipment_id',
        'ndt_request_id',
        'valid_until',
        'status',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'document_date' => 'date',
        'valid_until' => 'date',
        'status' => DocumentStatus::class,
        'deleted_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(NdtObject::class, 'object_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(NdtRequest::class, 'ndt_request_id');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'document_files')
            ->whereNull('files.deleted_at')
            ->withTimestamps();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'document_id');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class, 'document_id')->latestOfMany('version_number');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(DocumentRelation::class, 'document_id');
    }
}
