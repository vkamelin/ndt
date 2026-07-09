<?php

declare(strict_types=1);

namespace App\Modules\Employees\Models;

use App\Modules\Employees\Enums\QualificationMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EmployeeQualification extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'method',
        'valid_until',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'method' => QualificationMethod::class,
        'valid_until' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
