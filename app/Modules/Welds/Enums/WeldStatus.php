<?php

declare(strict_types=1);

namespace App\Modules\Welds\Enums;

enum WeldStatus: string
{
    case Created = 'created';
    case WaitingControl = 'waiting_control';
    case AssignedToTask = 'assigned_to_task';
    case InControl = 'in_control';
    case ControlCompleted = 'control_completed';
    case WaitingAnalysis = 'waiting_analysis';
    case Good = 'good';
    case Defect = 'defect';
    case RepairRequired = 'repair_required';
    case InRepair = 'in_repair';
    case WaitingRecheck = 'waiting_recheck';
    case Excluded = 'excluded';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Создан',
            self::WaitingControl => 'Ожидает контроля',
            self::AssignedToTask => 'Назначен в задание',
            self::InControl => 'В контроле',
            self::ControlCompleted => 'Контроль выполнен',
            self::WaitingAnalysis => 'Ожидает анализа',
            self::Good => 'Годен',
            self::Defect => 'Дефект',
            self::RepairRequired => 'Требуется ремонт',
            self::InRepair => 'В ремонте',
            self::WaitingRecheck => 'Ожидает повторного контроля',
            self::Excluded => 'Исключен',
            self::Closed => 'Закрыт',
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
