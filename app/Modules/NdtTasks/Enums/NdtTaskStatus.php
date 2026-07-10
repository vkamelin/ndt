<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Enums;

enum NdtTaskStatus: string
{
    case Created = 'created';
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case InWork = 'in_work';
    case Completed = 'completed';
    case Partial = 'partial';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Создано',
            self::Assigned => 'Назначено',
            self::Accepted => 'Принято исполнителем',
            self::InWork => 'В работе',
            self::Completed => 'Выполнено',
            self::Partial => 'Выполнено частично',
            self::Returned => 'Возвращено',
            self::Cancelled => 'Отменено',
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
