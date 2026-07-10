<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Enums;

enum RtStatus: string
{
    case Assigned = 'assigned';
    case Shot = 'shot';
    case LabTransferred = 'lab_transferred';
    case Processing = 'processing';
    case ReadyForDecoding = 'ready_for_decoding';
    case Decoding = 'decoding';
    case NeedsReshoot = 'needs_reshoot';
    case ReshootDone = 'reshoot_done';
    case Decoded = 'decoded';
    case SentToAnalysis = 'sent_to_analysis';
    case IncludedInConclusion = 'included_in_conclusion';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Assigned => 'Назначен',
            self::Shot => 'Снят',
            self::LabTransferred => 'Передан лаборанту',
            self::Processing => 'В обработке',
            self::ReadyForDecoding => 'Готов к дешифровке',
            self::Decoding => 'На дешифровке',
            self::NeedsReshoot => 'Требуется пересвет',
            self::ReshootDone => 'Пересвет выполнен',
            self::Decoded => 'Дешифрован',
            self::SentToAnalysis => 'Передан на анализ',
            self::IncludedInConclusion => 'Включен в заключение',
            self::Archived => 'Архивирован',
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
