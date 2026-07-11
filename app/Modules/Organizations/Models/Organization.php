<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Models;

use App\Modules\Objects\Models\NdtObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Organization extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_active',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'bool',
        'deleted_at' => 'datetime',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(OrganizationContact::class);
    }

    public function laboratories(): HasMany
    {
        return $this->hasMany(Laboratory::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(NdtObject::class);
    }
}
