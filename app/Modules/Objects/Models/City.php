<?php

declare(strict_types=1);

namespace App\Modules\Objects\Models;

use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class City extends Model
{
    use HasFactory;

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
    ];

    public function objects(): HasMany
    {
        return $this->hasMany(NdtObject::class, 'city_id');
    }

    public function organizations(): HasMany
    {
        return $this->hasMany(Organization::class);
    }
}
