<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Enums;

enum NdtResultStatus: string
{
    case Created = 'created';
    case InAnalysis = 'in_analysis';
    case Defect = 'defect';
    case ReadyForConclusion = 'ready_for_conclusion';
    case Returned = 'returned';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Создан',
            self::InAnalysis => 'Передан на анализ',
            self::Defect => 'С дефектом',
            self::ReadyForConclusion => 'Готов к заключению',
            self::Returned => 'Возвращен',
            self::Approved => 'Утвержден',
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
