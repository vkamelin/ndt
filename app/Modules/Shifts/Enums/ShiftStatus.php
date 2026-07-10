<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Enums;

enum ShiftStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case AwaitingCompletion = 'awaiting_completion';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Открыта',
            self::InProgress => 'В работе',
            self::AwaitingCompletion => 'Ожидает завершения',
            self::Completed => 'Завершена',
            self::Cancelled => 'Отменена',
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
