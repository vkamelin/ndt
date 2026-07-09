<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Enums;

enum NdtRequestStatus: string
{
    case Draft = 'draft';
    case Registered = 'registered';
    case Clarification = 'clarification';
    case Accepted = 'accepted';
    case Planned = 'planned';
    case InWork = 'in_work';
    case Partial = 'partial';
    case WaitingAnalysis = 'waiting_analysis';
    case Approval = 'approval';
    case Issued = 'issued';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Черновик',
            self::Registered => 'Зарегистрирована',
            self::Clarification => 'На уточнении',
            self::Accepted => 'Принята в работу',
            self::Planned => 'Запланирована',
            self::InWork => 'В работе',
            self::Partial => 'Частично выполнена',
            self::WaitingAnalysis => 'Ожидает анализа',
            self::Approval => 'На утверждении',
            self::Issued => 'Выдана',
            self::Closed => 'Закрыта',
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
