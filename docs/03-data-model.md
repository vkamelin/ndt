# Модель базы данных

## 1. Назначение

Документ фиксирует черновую модель БД для разработки. При реализации миграций Codex должен опираться на этот документ, `project.md`, `README.md` и `AGENTS.md`.

## 2. Общие правила БД

1. БД должна быть нормализованной и понятной.
2. Не собирать всю систему в одну широкую таблицу.
3. Не хранить методы контроля строкой через запятую.
4. Не хранить файлы в БД.
5. Хранить метаданные файлов в БД, сами файлы — в storage/S3-compatible хранилище.
6. Для обязательных связей использовать FK.
7. Для больших списков создавать индексы под реальные фильтры.
8. Для рабочих сущностей использовать soft delete или статус аннулирования, если данные могут быть связаны с документами.
9. Для справочников использовать деактивацию вместо физического удаления, если значение уже используется.
10. Критичные изменения должны попадать в audit log.

## 3. Пользователи и доступ

Таблицы:

- `users`;
- `roles`;
- `permissions`;
- `model_has_roles`;
- `role_has_permissions`;
- `employee_user`;
- `audit_logs`.

Основные связи:

- пользователь может быть связан с сотрудником;
- пользователь имеет роли;
- роль имеет permissions.

Минимальные поля `users`:

- `id`;
- `name`;
- `email`;
- `password`;
- `status`;
- `remember_token`;
- `created_at`;
- `updated_at`;
- `deleted_at`.

Статусы пользователя:

- `active`;
- `blocked`.

## 4. Города и объекты/участки

Таблицы:

- `cities`;
- `objects`.

`cities`:

- `id`;
- `name`;
- `is_active`;
- `comment`;
- timestamps.

`objects`:

- `id`;
- `city_id`;
- `name`;
- `code` nullable;
- `is_active`;
- `comment`;
- timestamps.

Индексы:

- `objects.city_id`;
- уникальность названия объекта в рамках города, если это будет утверждено.

## 5. Сотрудники

Таблицы:

- `employees`;
- `positions`;
- `employee_qualifications`;
- `employee_documents`;
- `employee_medical_checks`;
- `employee_trainings`.

`employees`:

- `id`;
- `object_id`;
- `position_id`;
- `last_name`;
- `first_name`;
- `middle_name` nullable;
- `phone` nullable;
- `email` nullable;
- `status`;
- `personnel_number` nullable;
- timestamps;
- soft deletes.

Индексы:

- `employees.object_id`;
- `employees.position_id`;
- `employees.status`.

## 6. Организации

Таблицы:

- `organizations`;
- `organization_contacts`;
- `laboratories`.

Назначение:

- заказчики;
- лаборатория;
- реквизиты;
- контактные лица.

Не добавлять лишние реквизиты без подтверждения из задачи или проектного документа.

## 7. Сварные стыки

Таблицы:

- `welds`;
- `weld_materials`;
- `weld_repairs`;
- `weld_status_history`.

`welds`:

- `id`;
- `object_id`;
- `title_id` nullable;
- `drawing_id` nullable;
- `line_id` nullable;
- `weld_number`;
- `diameter` nullable;
- `thickness` nullable;
- `material_1_id` nullable;
- `material_2_id` nullable;
- `welded_at` nullable;
- `welding_process_id` nullable;
- `weld_type_id` nullable;
- `pipeline_category_id` nullable;
- `medium_id` nullable;
- `pwht` nullable;
- `normative_document_id` nullable;
- `status`;
- timestamps;
- soft deletes.

Индексы:

- `(object_id, drawing_id, line_id, weld_number)`;
- `status`;
- `normative_document_id`.

## 8. Заявки

Таблицы:

- `ndt_requests`;
- `ndt_request_items`;
- `ndt_request_files`;
- `ndt_request_status_history`.

`ndt_requests`:

- `id`;
- `request_number`;
- `request_date`;
- `organization_id` nullable;
- `object_id`;
- `title_id` nullable;
- `priority` nullable;
- `due_date` nullable;
- `basis` nullable;
- `comment` nullable;
- `status`;
- timestamps;
- soft deletes.

Индексы:

- `request_number`;
- `object_id`;
- `status`;
- `request_date`.

## 9. Методы и задания

Таблицы:

- `ndt_methods`;
- `weld_ndt_methods`;
- `ndt_tasks`;
- `ndt_task_items`;
- `ndt_task_status_history`.

`ndt_methods` хранит справочник методов НК:

- РК;
- ВИК;
- ПВК;
- МК;
- УК.

`weld_ndt_methods` связывает стык и требуемый метод контроля.

`ndt_tasks`:

- `id`;
- `task_number`;
- `ndt_request_id`;
- `object_id`;
- `ndt_method_id`;
- `assignee_employee_id`;
- `planned_date` nullable;
- `priority` nullable;
- `comment` nullable;
- `status`;
- timestamps.

Индексы:

- `(object_id, assignee_employee_id, status, planned_date)`;
- `ndt_request_id`;
- `ndt_method_id`.

## 10. Результаты контроля

Таблицы:

- `ndt_results`;
- `ndt_result_defects`;
- `ndt_result_files`;
- `ndt_result_status_history`.

`ndt_results`:

- `id`;
- `weld_id`;
- `ndt_method_id`;
- `ndt_task_id` nullable;
- `executor_employee_id`;
- `controlled_at` nullable;
- `equipment_id` nullable;
- `normative_document_id` nullable;
- `result`;
- `status`;
- `comment` nullable;
- timestamps.

Индексы:

- `(weld_id, ndt_method_id, status)`;
- `executor_employee_id`;
- `equipment_id`.

## 11. Радиографический контроль

Таблицы:

- `rt_results`;
- `rt_films`;
- `rt_images`;
- `rt_exposures`;
- `rt_reshoots`;
- `rt_density_measurements`;
- `rt_archive_items`.

РК не должен быть только набором полей в общей таблице результатов, потому что у него есть отдельные процессы:

- пленки;
- снимки;
- пересветы;
- плотности;
- дешифровка;
- архив;
- реестры передачи.

## 12. ВИК / ПВК / МК / УК

Таблицы:

- `vt_results`;
- `pt_results`;
- `mt_results`;
- `ut_results`.

Каждая таблица расширяет общий результат метода, если для метода нужны специфические поля.

Не создавать метод-специфические поля в общей таблице `ndt_results`, если они относятся только к одному методу.

## 13. Заключения

Таблицы:

- `conclusions`;
- `conclusion_items`;
- `conclusion_versions`;
- `conclusion_files`;
- `conclusion_status_history`.

`conclusions`:

- `id`;
- `number`;
- `date`;
- `object_id`;
- `ndt_method_id`;
- `ndt_request_id` nullable;
- `prepared_by_employee_id` nullable;
- `checked_by_employee_id` nullable;
- `approved_by_employee_id` nullable;
- `status`;
- timestamps.

Индексы:

- `(object_id, number, date, status)`;
- `ndt_method_id`;
- `status`.

Утвержденное заключение нельзя редактировать напрямую.

## 14. Смены

Таблицы:

- `shifts`;
- `lab_shift_reports`;
- `lab_shift_regulatory_works`;
- `film_inventory_transactions`;
- `chemical_inventory_transactions`;
- `chemical_requests`;
- `decoder_shift_reports`;
- `decoder_film_groups`;
- `decoder_rejects`;
- `decoder_forgery_suspicions`;
- `decoder_cleanups`;
- `decoder_decryptions`.

`shifts`:

- `id`;
- `employee_id`;
- `object_id`;
- `type`;
- `status`;
- `started_at`;
- `finished_at` nullable;
- `comment` nullable;
- timestamps.

Уникальное ограничение для открытой смены одного типа на сотрудника должно быть реализовано на уровне бизнес-логики; если возможно — усилено индексом.

## 15. Оборудование

Таблицы:

- `equipment`;
- `equipment_types`;
- `equipment_verifications`;
- `equipment_calibrations`;
- `equipment_repairs`;
- `equipment_assignments`;
- `equipment_movements`;
- `equipment_defects`;
- `equipment_documents`.

Индексы:

- `(object_id, inventory_number, serial_number, status)`;
- `equipment_type_id`;
- `status`.

## 16. Документы и файлы

Таблицы:

- `document_types`;
- `documents`;
- `document_files`;
- `document_versions`;
- `document_relations`;
- `files`.

Файлы должны храниться приватно.

`files` хранит только метаданные:

- оригинальное имя;
- системное имя;
- путь в storage;
- MIME-type;
- размер;
- хеш;
- владелец;
- связанную сущность;
- статус;
- дату загрузки.

## 17. Справочники

Таблицы:

- `materials`;
- `welding_processes`;
- `weld_types`;
- `pipeline_categories`;
- `media`;
- `normative_documents`;
- `defect_types`;
- `result_statuses`;
- `register_types`;
- `act_types`;
- `film_types`;
- `chemical_types`.

Правило: справочники должны иметь `is_active`, если значения могут устаревать.
