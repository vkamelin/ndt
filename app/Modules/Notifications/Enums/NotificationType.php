<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Enums;

enum NotificationType: string
{
    case TaskAssigned = 'task_assigned';
    case TaskOverdue = 'task_overdue';
    case RequestClarification = 'request_clarification';
    case ResultWaitingAnalysis = 'result_waiting_analysis';
    case ConclusionWaitingApproval = 'conclusion_waiting_approval';
    case ConclusionReturned = 'conclusion_returned';
    case ReshootRequired = 'reshoot_required';
    case DefectFound = 'defect_found';
    case EquipmentVerificationExpiring = 'equipment_verification_expiring';
    case EquipmentCalibrationExpiring = 'equipment_calibration_expiring';
    case QualificationExpiring = 'qualification_expiring';
    case ShiftIncomplete = 'shift_incomplete';
    case ChemicalRequired = 'chemical_required';
    case QueueFailure = 'queue_failure';

    public function label(): string
    {
        return match ($this) {
            self::TaskAssigned => 'Назначено задание',
            self::TaskOverdue => 'Задание просрочено',
            self::RequestClarification => 'Заявка требует уточнения',
            self::ResultWaitingAnalysis => 'Результат ожидает анализа',
            self::ConclusionWaitingApproval => 'Заключение ожидает утверждения',
            self::ConclusionReturned => 'Заключение возвращено',
            self::ReshootRequired => 'Требуется пересвет',
            self::DefectFound => 'Обнаружен дефект',
            self::EquipmentVerificationExpiring => 'Заканчивается поверка оборудования',
            self::EquipmentCalibrationExpiring => 'Заканчивается калибровка оборудования',
            self::QualificationExpiring => 'Заканчивается удостоверение сотрудника',
            self::ShiftIncomplete => 'Смена не завершена',
            self::ChemicalRequired => 'Требуется химия',
            self::QueueFailure => 'Ошибка фоновой задачи',
        };
    }
}
