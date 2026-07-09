<?php

declare(strict_types=1);

namespace App\Modules\Employees\Enums;

enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активен',
            self::Inactive => 'Не активен',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
