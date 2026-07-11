<?php

declare(strict_types=1);

namespace App\Modules\Objects\Models;

use App\Modules\Organizations\Models\Organization;
use App\Modules\Employees\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class NdtObject extends Model
{
    use HasFactory;

    protected $table = 'objects';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'city_id',
        'organization_id',
        'name',
        'code',
        'is_active',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'bool',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'object_id');
    }
}
