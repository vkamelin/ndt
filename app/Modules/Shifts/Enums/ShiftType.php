<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Enums;

enum ShiftType: string
{
    case Lab = 'lab';
    case Decoder = 'decoder';

    public function label(): string
    {
        return match ($this) {
            self::Lab => 'Смена лаборанта',
            self::Decoder => 'Смена дешифровщика',
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
