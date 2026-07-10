<?php

declare(strict_types=1);

namespace App\Modules\Documents\Enums;

enum FileStatus: string
{
    case Active = 'active';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активен',
            self::Deleted => 'Удален',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
