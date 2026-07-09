<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активен',
            self::Blocked => 'Заблокирован',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isBlocked(): bool
    {
        return $this === self::Blocked;
    }
}
