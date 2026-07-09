<?php

declare(strict_types=1);

namespace App\Modules\Employees\Enums;

enum QualificationMethod: string
{
    case RK = 'rk';
    case VIK = 'vik';
    case PVK = 'pvk';
    case MK = 'mk';
    case UK = 'uk';

    public function label(): string
    {
        return match ($this) {
            self::RK => 'РК',
            self::VIK => 'ВИК',
            self::PVK => 'ПВК',
            self::MK => 'МК',
            self::UK => 'УК',
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
