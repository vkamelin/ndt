<?php

declare(strict_types=1);

namespace App\Modules\Documents\Enums;

enum DocumentVersionStatus: string
{
    case Current = 'current';
    case Superseded = 'superseded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Current => 'Текущая',
            self::Superseded => 'Заменена',
            self::Cancelled => 'Аннулирована',
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
