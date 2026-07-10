<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Enums;

enum EquipmentStatus: string
{
    case Available = 'available';
    case Issued = 'issued';
    case InRepair = 'in_repair';
    case Defective = 'defective';
    case WrittenOff = 'written_off';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Доступно',
            self::Issued => 'Выдано',
            self::InRepair => 'В ремонте',
            self::Defective => 'Неисправно',
            self::WrittenOff => 'Списано',
        };
    }

    public function isUsable(): bool
    {
        return in_array($this, [self::Available, self::Issued], true);
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
