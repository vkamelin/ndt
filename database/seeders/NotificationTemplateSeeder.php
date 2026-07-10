<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Notifications\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

final class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'task_assigned',
                'name' => 'Назначение задания',
                'title' => 'Назначено задание {{task_number}}',
                'subject' => 'Назначено задание {{task_number}}',
                'body' => 'Вам назначено задание {{task_number}} по методу {{method_label}}. Объект: {{object_name}}.',
            ],
            [
                'code' => 'task_overdue',
                'name' => 'Просроченное задание',
                'title' => 'Задание {{task_number}} просрочено',
                'subject' => 'Задание {{task_number}} просрочено',
                'body' => 'Задание {{task_number}} по методу {{method_label}} просрочено. Срок был: {{planned_date}}.',
            ],
            [
                'code' => 'request_clarification',
                'name' => 'Заявка требует уточнения',
                'title' => 'Заявка {{request_number}} требует уточнения',
                'subject' => 'Заявка {{request_number}} требует уточнения',
                'body' => 'Заявка {{request_number}} по объекту {{object_name}} переведена на уточнение.',
            ],
            [
                'code' => 'result_waiting_analysis',
                'name' => 'Результат ожидает анализа',
                'title' => 'Результат по стыку {{weld_number}} ожидает анализа',
                'subject' => 'Результат по стыку {{weld_number}} ожидает анализа',
                'body' => 'Результат {{result_id}} по стыку {{weld_number}} и методу {{method_label}} ожидает анализа.',
            ],
            [
                'code' => 'conclusion_waiting_approval',
                'name' => 'Заключение ожидает утверждения',
                'title' => 'Заключение {{conclusion_number}} ожидает утверждения',
                'subject' => 'Заключение {{conclusion_number}} ожидает утверждения',
                'body' => 'Заключение {{conclusion_number}} по объекту {{object_name}} передано на утверждение.',
            ],
            [
                'code' => 'conclusion_returned',
                'name' => 'Заключение возвращено',
                'title' => 'Заключение {{conclusion_number}} возвращено',
                'subject' => 'Заключение {{conclusion_number}} возвращено',
                'body' => 'Заключение {{conclusion_number}} по объекту {{object_name}} возвращено на доработку.',
            ],
            [
                'code' => 'reshoot_required',
                'name' => 'Требуется пересвет',
                'title' => 'Для снимка {{rt_result_id}} требуется пересвет',
                'subject' => 'Для снимка {{rt_result_id}} требуется пересвет',
                'body' => 'По результату {{rt_result_id}} зафиксирован пересвет. Нужна повторная обработка.',
            ],
            [
                'code' => 'defect_found',
                'name' => 'Обнаружен дефект',
                'title' => 'По результату {{result_id}} обнаружен дефект',
                'subject' => 'По результату {{result_id}} обнаружен дефект',
                'body' => 'По результату {{result_id}} по стыку {{weld_number}} зафиксирован дефект.',
            ],
            [
                'code' => 'equipment_verification_expiring',
                'name' => 'Поверка оборудования заканчивается',
                'title' => 'Поверка оборудования {{equipment_name}} заканчивается',
                'subject' => 'Поверка оборудования {{equipment_name}} заканчивается',
                'body' => 'У оборудования {{equipment_name}} срок поверки заканчивается {{valid_until}}.',
            ],
            [
                'code' => 'equipment_calibration_expiring',
                'name' => 'Калибровка оборудования заканчивается',
                'title' => 'Калибровка оборудования {{equipment_name}} заканчивается',
                'subject' => 'Калибровка оборудования {{equipment_name}} заканчивается',
                'body' => 'У оборудования {{equipment_name}} срок калибровки заканчивается {{valid_until}}.',
            ],
            [
                'code' => 'qualification_expiring',
                'name' => 'Удостоверение сотрудника заканчивается',
                'title' => 'Удостоверение {{employee_name}} заканчивается',
                'subject' => 'Удостоверение {{employee_name}} заканчивается',
                'body' => 'У сотрудника {{employee_name}} по методу {{method_label}} срок действия заканчивается {{valid_until}}.',
            ],
            [
                'code' => 'shift_incomplete',
                'name' => 'Смена не завершена',
                'title' => 'Смена {{shift_id}} не завершена',
                'subject' => 'Смена {{shift_id}} не завершена',
                'body' => 'Смена {{shift_id}} по объекту {{object_name}} требует завершения.',
            ],
            [
                'code' => 'chemical_required',
                'name' => 'Требуется химия',
                'title' => 'Требуется химия по смене {{shift_id}}',
                'subject' => 'Требуется химия по смене {{shift_id}}',
                'body' => 'По смене {{shift_id}} был оформлен запрос химии {{chemical_name}}.',
            ],
            [
                'code' => 'report_ready',
                'name' => 'Отчет готов',
                'title' => 'Отчет {{report_title}} готов',
                'subject' => 'Отчет {{report_title}} готов',
                'body' => 'Отчет {{report_title}} по объекту {{object_name}} готов к скачиванию.',
            ],
            [
                'code' => 'queue_failure',
                'name' => 'Ошибка фоновой задачи',
                'title' => 'Ошибка фоновой задачи',
                'subject' => 'Ошибка фоновой задачи',
                'body' => '{{message}}',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::query()->updateOrCreate(
                ['code' => $template['code']],
                $template + [
                    'channels' => ['database', 'email'],
                    'meta' => null,
                    'is_active' => true,
                ],
            );
        }
    }
}
