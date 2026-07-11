<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Employees\Models\Employee;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'status',
        'password',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => UserStatus::class,
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Default guard for roles and permissions.
     */
    protected string $guard_name = 'web';

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isBlocked(): bool
    {
        return $this->status === UserStatus::Blocked;
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_user')
            ->withTimestamps();
    }

    public function primaryEmployee(): ?Employee
    {
        return $this->employees()->first();
    }

    public function objectId(): ?int
    {
        return $this->primaryEmployee()?->object_id;
    }

    public function systemNotifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function unreadSystemNotifications(): HasMany
    {
        return $this->systemNotifications()->whereNull('read_at');
    }
}
