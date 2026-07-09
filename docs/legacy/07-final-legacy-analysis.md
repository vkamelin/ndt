# Анализ старой версии приложения для проектирования новой системы

## 1. Назначение документа

Этот документ объединяет выводы из ранее подготовленных исследовательских отчётов по старой версии приложения и фиксирует их в одном месте для использования при проектировании новой системы.

Цель документа:
- зафиксировать фактическое устройство старого приложения;
- отделить подтверждённые факты от предположений;
- выделить противоречия и незавершённые части;
- показать, что можно переиспользовать в новой версии, а что требует проверки.

Основные источники этого документа:
- [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md)
- [02-database-and-entities.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/02-database-and-entities.md)
- [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md)
- [04-roles-permissions-statuses.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/04-roles-permissions-statuses.md)
- [05-forms-reports-documents-files.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/05-forms-reports-documents-files.md)
- [06-business-logic-and-gaps.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/06-business-logic-and-gaps.md)

## 2. Исходные материалы

Использованы только ранее подготовленные отчёты по коду старой версии приложения. Повторный анализ кода с нуля не выполнялся.

Дополнительно учитывались прямые ссылки внутри отчётов на:
- маршруты;
- контроллеры;
- модели;
- миграции;
- сидеры;
- политики;
- сервисы;
- frontend-страницы и модалки;
- тесты.

## 3. Краткое резюме анализа

Старая версия приложения представляет собой модульный монолит на Laravel + Inertia.js, ориентированный на учёт и сопровождение процессов НК, кадровый учёт, учёт оборудования, документооборот, сменные workflows и журналы операций.

Ключевые домены:
- Core: пользователи, роли, сотрудники, филиалы, объекты, должности, очереди;
- Organizations: контрагенты и реквизиты;
- Equipment: жизненный цикл оборудования и сопутствующие журналы;
- Documents: единый реестр документов, файлов, версий и связей;
- Shifts: сменные сценарии для лаборанта и расшифровщика;
- Notifications: журнал доставки уведомлений.

Система уже содержит:
- RBAC на базе Spatie Permission;
- policies и middleware для backend-авторизации;
- Inertia/Vue frontend со списками, фильтрами, модалками и карточками;
- экспорт в Excel и печатные формы/PDF для отдельных сущностей;
- файловое хранилище с приватной выдачей через signed URL.

Недостаточно данных для точного вывода по отдельным второстепенным веткам UI, демо-экспортам и части незавершённых методов. Эти места отдельно зафиксированы ниже.

## 4. Общая структура старого проекта

### 4.1 Общая организация

Проект построен как модульный монолит. По отчёту [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md) основные части расположены так:
- `app/` - backend Laravel;
- `app/Modules/Core/` - базовый функционал;
- `app/Modules/Organizations/` - организации и реквизиты;
- `app/Modules/Equipment/` - оборудование и журналы жизненного цикла;
- `app/Modules/Documents/` - документы, версии, файлы, связи;
- `app/Modules/Shifts/` - сменные workflows;
- `resources/js/` - Vue/Inertia frontend;
- `database/` - миграции и seeders;
- `routes/` - web/api/console маршруты;
- `storage/` - хранилище файлов и служебных данных;
- `tests/` - unit/feature тесты.

### 4.2 Модульная структура

Основные модули:
- Core;
- Organizations;
- Equipment;
- Documents;
- Shifts.

Вывод отчёта [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md): это именно модульный монолит, а не набор независимых сервисов.

### 4.3 Frontend-структура

По отчётам [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md) и [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md):
- входная точка frontend - `resources/js/app.ts`;
- layout-слой разделён на глобальный и модульные layout-файлы;
- страницы обычно лежат в `resources/js/modules/*/Pages/*`;
- формы часто реализованы через modal-компоненты;
- документы используют отдельные Create/Edit страницы;
- списки обычно содержат фильтры, сортировку и пагинацию.

## 5. Назначение старой системы

По совокупности отчётов [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md), [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md), [05-forms-reports-documents-files.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/05-forms-reports-documents-files.md):

- система предназначена для учёта процессов неразрушающего контроля;
- в ней также есть кадровый контур, учёт оборудования, документы, организационная структура и сменные производственные сценарии;
- старая версия закрывает как административные функции, так и операционные журналы;
- есть роли для лабораторного и decoder-сценариев смен;
- есть отчёты, экспорт и карточки сущностей для прикладного использования.

Недостаточно данных для точного вывода о том, какой из доменов был главным бизнес-ядром в исходной постановке. Код и отчёты показывают сразу несколько крупных подсистем.

## 6. Технологический стек и архитектура

### 6.1 Backend

Подтверждённый стек по [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md):
- PHP 8.4;
- Laravel 12;
- Eloquent ORM;
- Inertia.js;
- Spatie Permission;
- Spatie Activitylog;
- Laravel Sanctum;
- Laravel Horizon;
- Laravel Octane;
- Maatwebsite Excel;
- Intervention Image;
- Barryvdh DomPDF;
- Spatie Laravel PDF.

### 6.2 Frontend

Подтверждённый стек:
- Vue 3;
- Composition API;
- TypeScript;
- Inertia.js;
- Vite 7;
- Tabler UI;
- Tabler Icons;
- Tailwind CSS 4;
- Axios.

### 6.3 Инфраструктура и хранение

По [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md) и [05-forms-reports-documents-files.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/05-forms-reports-documents-files.md):
- SQLite используется по умолчанию;
- PostgreSQL 16+ заявлен для production;
- Redis 7+ используется в production;
- для разработки используются database cache/queue/session;
- файлы выдаются через signed URL и приватное хранилище.

### 6.4 Архитектурные признаки

Подтверждено:
- модульный монолит;
- Inertia.js как способ связывания backend и frontend;
- DTO + FormRequest + service слой;
- policies и permissions как основа авторизации;
- таблицы-журналы и отдельные workflow-сервисы для сложных сценариев;
- приватное файловое хранилище с signed download flow.

## 7. Доменные сущности

### 7.1 Core

Подтверждённые сущности по [02-database-and-entities.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/02-database-and-entities.md):
- User;
- Role;
- Permission;
- Employee;
- Position;
- Branch;
- BranchPhone;
- BranchEmail;
- ProjectObject;
- ObjectEquipment;
- NotificationDelivery;
- FilmType.

### 7.2 Organizations

- Organization;
- OrganizationContact;
- OrganizationBankAccount.

### 7.3 Equipment

- EquipmentType;
- Equipment;
- EquipmentVerification;
- EquipmentCalibration;
- EquipmentMaintenance;
- EquipmentAssignment;
- EquipmentMovement;
- EquipmentDocument;
- EquipmentDefect.

### 7.4 Documents

- DocumentType;
- Document;
- DocumentFile;
- DocumentVersion;
- DocumentRelation;
- DocumentTag;
- DocumentTagAssignment.

### 7.5 Shifts

- Shift;
- ShiftLabassistant;
- FilmInventoryTransaction;
- DevelopingChemicalTransaction;
- DevelopingMachineMaintenanceLog;
- ChemicalRequest;
- DecoderShiftFilmGroup;
- DecoderShiftReject;
- DecoderShiftForgerySuspicion;
- DecoderShiftCleanup;
- DecoderShiftDecryption;
- RsType.

## 8. Модель базы данных

### 8.1 Общая картина

По [02-database-and-entities.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/02-database-and-entities.md):
- в БД есть system/infrastructure tables;
- есть крупные доменные таблицы;
- есть pivot-таблицы и журнальные таблицы;
- есть soft deletes и timestamps;
- есть timezone-aware и timezone-unaware поля;
- есть mix прямых таблиц и таблиц без локальных моделей.

### 8.2 Ключевые группы таблиц

#### System / infrastructure
- `users`;
- `roles`;
- `permissions`;
- pivot-таблицы Spatie;
- `activity_log`;
- `cache`, `cache_locks`;
- `jobs`, `job_batches`, `failed_jobs`;
- `sessions`, `password_reset_tokens`, `personal_access_tokens`.

#### Core HR
- `employees`;
- `employee_qualifications`;
- `employee_medical_examinations`;
- `employee_briefings`;
- `employee_ppe_items`;
- `employee_trainings`;
- `employee_documents`;
- `branches`;
- `branch_phones`;
- `branch_emails`;
- `positions`;
- `regions`.

#### Organizations / Objects
- `organizations`;
- `organization_contacts`;
- `organization_bank_accounts`;
- `objects`;
- `objects_customers`;
- `object_equipment`.

#### Equipment
- `equipment_types`;
- `equipment`;
- `equipment_verifications`;
- `equipment_calibrations`;
- `equipment_maintenances`;
- `equipment_assignments`;
- `equipment_movements`;
- `equipment_documents`;
- `equipment_defects`.

#### Documents
- `document_types`;
- `documents`;
- `document_files`;
- `document_versions`;
- `document_relations`;
- `document_tags`;
- `document_tag_assignments`.

#### Shifts
- `shifts`;
- `shifts_labassistant`;
- `film_types`;
- `film_inventory_transactions`;
- `developing_chemical_transactions`;
- `developing_machine_maintenance_logs`;
- `chemical_requests`;
- `decoder_shift_*`;
- `rs_types`.

#### Notifications
- `notification_deliveries`;
- `telegram_messages`;
- `sms_messages`;
- `max_messages`.

### 8.3 Сильные стороны модели

По [02-database-and-entities.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/02-database-and-entities.md):
- много явных FK;
- есть отдельные журналы под операции;
- есть versioning для документов;
- есть централизованный журнал уведомлений;
- есть pivot-таблицы для связей объектов, ролей и прав.

### 8.4 Слабые стороны модели

По тому же отчёту:
- смешение смыслов в `employees`, `equipment`, `documents`;
- часть таблиц не имеет локальных Eloquent-моделей;
- часть файловых ссылок хранится строкой без FK;
- часть полей дублирует друг друга как агрегаты;
- смешаны timezone-aware и timezone-unaware даты;
- местами есть расхождение между моделями, DTO и миграциями.

## 9. Связи между сущностями

### 9.1 Подтверждённые FK и связи

По [02-database-and-entities.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/02-database-and-entities.md):
- Employee -> User / Position / Branch / ProjectObject;
- Branch -> Employee;
- ProjectObject -> Branch / Organization / Employee;
- Organization -> Region / User;
- Document -> DocumentType / Organization / Branch / Employee / User;
- Equipment -> EquipmentType / Branch / Employee / User;
- Shift -> Employee / ProjectObject;
- ChemicalRequest -> Employee / ProjectObject / Shift;
- FilmInventoryTransaction -> Employee / ProjectObject / Shift / FilmType;
- DevelopingChemicalTransaction -> Employee / ProjectObject / Shift / Equipment;
- NotificationDelivery -> Employee / User.

### 9.2 Таблицы связей

- `objects_customers`: objects ↔ organizations;
- `object_equipment`: objects ↔ equipment;
- `document_tag_assignments`: documents ↔ document_tags;
- `role_has_permissions`: roles ↔ permissions;
- `model_has_roles`: polymorphic model ↔ role;
- `model_has_permissions`: polymorphic model ↔ permission;
- `document_relations`: document ↔ document.

### 9.3 Логические связи

По отчёту [02-database-and-entities.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/02-database-and-entities.md):
- `Document.owner_type/owner_id` - polymorphic owner;
- `Shift.workflow` - discriminator сценария;
- `notification_deliveries.dedup_key` - ключ идемпотентности;
- `equipment.verification_document_file_id` и `calibration_document_file_id` выглядят как доменные ссылки, но FK в БД не оформлены.

## 10. Роли пользователей и права доступа

### 10.1 Подтверждённые роли

По [04-roles-permissions-statuses.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/04-roles-permissions-statuses.md):
- `admin`;
- `lab`;
- `decoder`;
- `user`.

### 10.2 Спорные роли

По тому же отчёту:
- `hr` - отражена в `PositionSeeder`, но не подтверждена как реальная роль доступа;
- `defectoscopist` - аналогично.

### 10.3 Модель прав

Подтверждено:
- используется Spatie Permission;
- permissions имеют формат `entity.action`;
- wildcard выключены;
- `admin` получает bypass через `Gate::before`;
- permissions создаются через seeders;
- в permissions есть descriptions.

### 10.4 Фактическое распределение прав

По [04-roles-permissions-statuses.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/04-roles-permissions-statuses.md):
- `admin` получает все permissions;
- `lab` получает права на профиль смен, медосмотры, инструктажи, СИЗ, часть сменных операций и отчёты;
- `decoder` получает права на decode-сценарий, связанные журналы и отчёты;
- `user` в seed-данных прав не получил, поэтому его реальный набор действий не подтверждён.

### 10.5 Где проверяется доступ

Подтверждено:
- middleware;
- policies;
- controller-level authorize/abort;
- service guards;
- frontend скрывает элементы UI, но не заменяет backend-проверки.

## 11. Маршруты, страницы и пользовательские сценарии

### 11.1 Общая карта маршрутов

По [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md):
- `/login`, `/logout`;
- `/`;
- `/profile` и связанные личные экраны;
- `/users`, `/roles`, `/employees`, `/branches`, `/objects`, `/positions`, `/queues`;
- `/organizations`, `/documents`, `/equipment`;
- `/shifts/*` и журналы смен;
- `/api/user`;
- `/files/{token}`;
- `/exports/examples/*`.

### 11.2 Страницы

Подтверждённые группы страниц:
- Dashboard;
- login;
- профиль и личные журналы;
- списки и карточки пользователей, ролей, сотрудников, филиалов, объектов, должностей;
- организации и их карточка;
- документы и карточка документа;
- оборудование и журналы жизненного цикла;
- сменные журналы и отчёты;
- queues page.

### 11.3 Типовые сценарии

По отчёту [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md):
- вход в систему;
- работа с профилем;
- управление сотрудниками;
- управление организациями;
- управление объектами;
- управление документами;
- управление оборудованием;
- сменные сценарии лаборанта;
- сменные сценарии расшифровщика;
- работа с отчётами и журналами;
- мониторинг очередей и уведомлений.

### 11.4 Важные сценарные особенности

- `profile` и `/profile` фактически ведут в dashboard;
- документы, equipment и organization используют смесь page + JSON edit endpoint;
- часть форм открывается через модалки;
- страницы отчётов смен используются как итоговые сводки по операциям.

## 12. API

### 12.1 Формальный API

По [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md):
- единственный явно подтверждённый public API endpoint: `GET /api/user`;
- доменные REST API в старой версии развиты слабо;
- основной контур общения backend/frontend идёт через web/Inertia routes.

### 12.2 JSON endpoints, используемые как UI API

Подтверждены endpoints для:
- edit-модалок пользователей;
- ролей;
- филиалов;
- организаций;
- оборудования;
- журналов оборудования;
- профиля уведомлений;
- состояния смены;
- загрузки файлов сотрудников и оборудования.

### 12.3 Особенности API

- есть служебный file-download endpoint;
- есть demo/export example routes;
- есть отдельные routes для связанных действий в документах;
- часть frontend-вызовов и backend-endpoint-ов расходится, это отдельно зафиксировано ниже.

## 13. Формы ввода

### 13.1 Core формы

Подтверждены:
- пользователь;
- роль;
- сотрудник;
- филиал;
- должность;
- объект.

Характеристика:
- обычно используются `FormRequest` + DTO + service;
- часто применяются модальные окна;
- у сотрудника форма большая и многораздельная;
- у объекта и организации есть карточки с вкладками.

### 13.2 Organizations

Подтверждены формы:
- организация;
- контакт организации;
- банковские реквизиты организации.

### 13.3 Documents

Подтверждены формы:
- тип документа;
- документ;
- файл документа;
- связь документов;
- версия документа.

### 13.4 Equipment

Подтверждены формы:
- оборудование;
- тип оборудования;
- поверка;
- калибровка;
- техническое обслуживание;
- закрепление оборудования;
- перемещение оборудования;
- дефект оборудования;
- документ оборудования.

### 13.5 Shifts

Подтверждены формы:
- старт смены;
- завершение смены лаборанта;
- регламентные работы смены;
- запрос плёнки;
- поступление плёнки;
- выдача плёнки;
- запрос химии;
- поступление химии;
- замена химии;
- завершение запроса химии;
- просмотренная плёнка;
- брак;
- подозрение на подлог;
- очистка рабочего места;
- дешифровка.

## 14. Отчеты, документы и печатные формы

### 14.1 Отчёты смен

По [05-forms-reports-documents-files.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/05-forms-reports-documents-files.md):
- отчёты смен лаборантов;
- отчёты смен расшифровщиков;
- журналы плёнки, химии, запросов химии;
- журналы просмотренной плёнки, брака, подлога, дешифровки;
- персональная страница `Мои отчеты`.

### 14.2 Документы

Подтверждено:
- карточка документа;
- вкладки:
  - основные данные;
  - реквизиты;
  - привязка;
  - сроки и контроль;
  - связанные данные;
- список файлов;
- список версий;
- список связей;
- типы документов как отдельный справочник.

### 14.3 Печатные формы

Подтверждены:
- PDF-печать карточки организации;
- печать карточки сотрудника.

### 14.4 Демонстрационные шаблоны

В старом проекте есть demo/export examples:
- реестр заявок РК;
- сотрудники;
- PDF-акт;
- PDF-реестр сотрудников.

Это зафиксировано как демонстрационный функционал, а не подтверждённое обязательное бизнес-требование.

## 15. Справочники

Подтверждённые справочники по [02-database-and-entities.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/02-database-and-entities.md) и [05-forms-reports-documents-files.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/05-forms-reports-documents-files.md):
- `roles`;
- `permissions`;
- `positions`;
- `regions`;
- `film_types`;
- `document_types`;
- `equipment_types`;
- `rs_types`;
- `document_tags`.

Также подтверждены enum-справочники внутри форм и миграций:
- статусы пользователей;
- статусы сотрудников;
- статусы документов;
- типы и статусы оборудования;
- типы и статусы поверок, калибровок, ТО, перемещений и дефектов;
- статусы смен и химических запросов;
- типы file roles у документов;
- типы связей документов;
- типы плёнки и химии;
- типы результатов медосмотров.

## 16. Статусы и жизненные циклы

### 16.1 Пользователи

По [04-roles-permissions-statuses.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/04-roles-permissions-statuses.md):
- `active`;
- `blocked`.

Жизненный цикл:
- создание;
- назначение роли;
- активный/заблокированный;
- blocked пользователь не может продолжать работу и принудительно разлогинивается.

### 16.2 Сотрудники

Статусы:
- `active`;
- `vacation`;
- `sick_leave`;
- `maternity_leave`;
- `business_trip`;
- `terminated`;
- `suspended_hr`.

### 16.3 Документы

Статусы:
- `draft`;
- `active`;
- `expired`;
- `terminated`;
- `archived`;
- `superseded`;
- `cancelled`.

Жизненный цикл:
- draft;
- active;
- возможное завершение через expiration/termination/archive/supersede/cancelled.

### 16.4 Оборудование

Основной статус:
- `active`;
- `in_storage`;
- `under_maintenance`;
- `under_calibration`;
- `retired`;
- `written_off`.

Сопутствующие жизненные статусы:
- condition;
- verification_status;
- calibration_status;
- repair_status;
- статус внутренних журналов проверки, калибровки, ТО, выдачи, перемещения, дефектов.

### 16.5 Смены

Статусы:
- `open`;
- `closed`;
- `cancelled`.

Жизненный цикл:
- открытие;
- рабочие операции;
- завершение;
- возможная отмена.

### 16.6 Химические запросы

Статусы:
- `pending`;
- `completed`;
- `cancelled`.

### 16.7 Уведомления и очереди

Статусы доставки:
- `pending`;
- `processing`;
- `sent`;
- `failed`;
- `skipped`.

Для channel-specific сообщений:
- `pending`;
- `processing`;
- `success`;
- `failed`.

## 17. Файлы, вложения и архив

### 17.1 Файлы

По [05-forms-reports-documents-files.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/05-forms-reports-documents-files.md):
- загрузка и выдача файлов идёт через `app/Services/FileStorageService.php`;
- файлы хранятся приватно и шифруются;
- ссылка выдаётся через signed route;
- используется `FileDownloadController`.

### 17.2 Вложения сотрудников

Подтверждены:
- документы сотрудника;
- сканы медосмотров;
- ограничение размера `max:10240`.

### 17.3 Вложения оборудования

Подтверждены файлы для:
- поверки;
- калибровки;
- обслуживания;
- закрепления;
- перемещения;
- дефектов;
- документов оборудования.

### 17.4 Вложения документов

Подтверждена таблица `document_files` и роли файлов:
- `primary`;
- `attachment`;
- `scan`;
- `signed_copy`;
- `generated`.

### 17.5 Архив

По [05-forms-reports-documents-files.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/05-forms-reports-documents-files.md):
- для документов архивируется через статус `archived`, `archive_date` и soft delete;
- у оборудования архивность и вывод из эксплуатации отражаются статусом и датой retired/write_off;
- для справочников и ряда сущностей используется soft delete;
- для сменных журналов отдельный архивный сценарий не подтверждён.

Недостаточно данных для точного вывода о едином архивном процессе для всех доменов.

## 18. Поиск, фильтры и списки

По [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md):
- большинство списков работают через query-параметры;
- используются debounce-фильтры;
- почти везде есть пагинация;
- часто есть сортировка;
- UI опирается на badges статусов и modal create/edit.

Типовые фильтры:
- пользователи: search, roles, status;
- роли: search;
- сотрудники: search, status;
- филиалы: name, responsible_employee_id, is_active;
- объекты: q, branch_id, operating_organization_id, responsible_employee_id, date ranges;
- документы: type, status, owner, dates, flags;
- оборудование: search, type, status, condition, branch, responsible, verification/calibration status;
- журналы смен: date ranges, status, object, employee, scenario-specific flags;
- очереди: search, status, channel, priority.

## 19. Бизнес-логика, найденная в коде

Сводные выводы из [06-business-logic-and-gaps.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/06-business-logic-and-gaps.md):

- blocked пользователь немедленно разлогинивается middleware;
- `admin` получает глобальный bypass через `Gate::before`;
- права в основном построены на `entity.action` permissions;
- индекс документов доступен по `document.view_any` и/или `document.view`;
- очередь требует одновременно `queue.view_any` и `notification_delivery.view_any`;
- смена связана с пользователем через `Employee`, а не напрямую;
- начать смену можно только один раз в день на сотрудника;
- завершение смены разделено на `lab` и `decoder` сценарии;
- для `decoder` обязательны журнал просмотренной плёнки и уборка;
- для `lab` при завершении сохраняются регламентные работы и `shifts_labassistant`;
- управление химией и плёнкой привязано к текущей открытой смене и текущему объекту;
- оборудование с определёнными статусами и критическими дефектами нельзя выдавать;
- документ версионируется при создании и обновлении;
- файлы раздаются через signed URL;
- уведомления используют dedupKey и выбор каналов по флагам сотрудника;
- автоматизация реализована в доменных сервисах, а не только в контроллерах.

## 20. Незавершенные части проекта

По [01-project-structure.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/01-project-structure.md), [03-routes-ui-api.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/03-routes-ui-api.md), [04-roles-permissions-statuses.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/04-roles-permissions-statuses.md), [06-business-logic-and-gaps.md](/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/docs/legacy-app-research/06-business-logic-and-gaps.md):

- `UserController` содержит TODO-методы `show`, `profile`, `updateProfile`, `changePassword`;
- `PositionService` содержит отключённые проверки зависимостей от пользователей;
- `PositionListItemData` держит `users_count` и `active_users_count` в `null`;
- `ShiftsServiceProvider` пустой;
- в `FilmInventoryService` есть закомментированный email-блок;
- `IndexController` существует, но не используется в маршрутах;
- demo-страницы `Index.vue` и `PageTemplate.vue` выглядят как небоевой UX-остаток;
- часть supplemental document routes есть в backend, но явный прямой UI-вызов не найден;
- restore-кнопка для soft-delete сценария организаций не подтверждена в UI;
- часть демо-экспортов выглядит как вспомогательная, а не бизнес-обязательная функциональность.

## 21. Расхождения между документацией и кодом

Зафиксированные противоречия и несостыковки:

- `Position::users()` объявлена в модели, но в БД нет `users.position_id`;
- в `PositionSeeder` есть `hr` и `defectoscopist`, но как реальные роли доступа они не подтверждены seed-данными ролей;
- `QueueController` и `Queues/Index.vue` расходятся по URL и bulk-операциям;
- `document.view` и `document.view_any` оба фигурируют как права доступа к индексу документов; если где-то ожидалось только одно из них, это расхождение;
- `organization restore` поддержан backend-ом, но в UI не найден явный вход;
- `notification_deliveries.channel = max` поддержан схемой, но рабочий транспортный клиент не найден;
- `IndexController` и demo-страницы существуют, но не участвуют в основной навигации;
- часть DTO и отчётных заметок содержит поля, отсутствующие в миграции или наоборот, что требует аккуратной проверки при переносе.

## 22. Что можно переиспользовать в новой системе

По совокупности отчётов можно использовать:

- модульный монолит как организационный принцип;
- RBAC-модель с `roles`, `permissions`, pivot-таблицами и descriptions;
- blocked-user middleware;
- разделение ролей `lab` и `decoder`;
- структуру списков с фильтрами, пагинацией и modals;
- карточки с вкладками для объектов, организаций, сотрудников, документов и оборудования;
- приватное хранение файлов с signed URL;
- версионирование документов;
- журналы сменных операций;
- отдельные сущности для жизненного цикла оборудования;
- отдельный workflow-сервисный слой для смен, оборудования и документов.

## 23. Что нельзя переносить без проверки

Нельзя переносить без подтверждения заказчика:

- `hr` и `defectoscopist` как полноценные роли доступа;
- `user` как роль с понятным набором прав;
- `Position::users()` без добавления реальной связи в БД;
- `QueueController` routing и bulk-операции без сверки с актуальным UX;
- demo export/print examples как обязательный функционал;
- hardcoded email и подобные operational-остатки;
- незавершённые `TODO`-методы в `UserController`;
- `IndexController` как рабочую часть системы;
- union статусов и lifecycle-правил оборудования без пересмотра доменной модели;
- timezone-unaware поля без явной политики хранения и отображения.

## 24. Требования к новой системе

Ниже сформулированы требования только на основе подтверждённых фактов старого проекта.

### 24.1 Функциональные требования

- Система должна поддерживать модульную структуру по доменам Core, Organizations, Equipment, Documents, Shifts;
- должна быть реализована авторизация на базе RBAC;
- должен быть dashboard с быстрыми действиями и личными журналами;
- должен быть кадровый учёт сотрудников;
- должны поддерживаться организации, объекты, филиалы и должности;
- должен быть реестр оборудования и его жизненного цикла;
- должен быть реестр документов с файлами, версиями и связями;
- должны быть сменные сценарии lab и decoder;
- должны быть отчёты и журналы по сменам;
- должен быть экспорт в Excel для ключевых реестров и журналов;
- должны быть печатные формы для части сущностей.

### 24.2 Требования к данным

- данные должны храниться с учётом реальной схемы и типов полей;
- нужно учитывать soft delete там, где он уже использовался;
- нужно сохранять связи между сотрудниками, филиалами, объектами, организациями и оборудованием;
- нужно предусмотреть версионирование документов;
- нужно предусмотреть историю жизненного цикла оборудования;
- необходимо различать timezone-aware и timezone-unaware даты;
- нужно сохранять файловые метаданные и роль файла;
- нужен централизованный журнал уведомлений.

### 24.3 Требования к ролям и правам

- права должны быть атомарными и именоваться в формате `entity.action`;
- RBAC должен быть источником истины;
- `admin` должен иметь полный доступ только если это подтверждено бизнесом;
- роли `lab` и `decoder` должны остаться как операционные роли только если это подтверждено заказчиком;
- backend-проверки обязательны;
- frontend-скрытие кнопок не заменяет backend-авторизацию.

### 24.4 Требования к формам

- должны быть формы CRUD для основных сущностей;
- сложные сущности должны иметь карточки с вкладками;
- для ряда операций допустимы модалки;
- связанные сущности должны загружаться через отдельные формы или секции внутри карточки;
- валидация должна проверять существование FK и зависимые даты/статусы;
- размеры файлов должны быть ограничены;
- формы смен должны учитывать role-specific сценарии.

### 24.5 Требования к отчетам

- должны быть отчёты смен лаборанта и расшифровщика;
- должны быть журналы операций по плёнке, химии, запросам химии, браку, подлогу и дешифровке;
- должен быть personal reports entry point;
- отчёты должны поддерживать просмотр карточки и, где применимо, экспорт в Excel.

### 24.6 Требования к документам

- должна быть карточка документа;
- должен быть справочник типов документов;
- должны поддерживаться статусы документа;
- должна быть версия документа;
- должны поддерживаться связи между документами;
- должны поддерживаться роли файлов;
- должны поддерживаться признаки конфиденциальности, оригинала, подписи, контроля сроков.

### 24.7 Требования к файлам

- хранение файлов должно быть приватным;
- скачивание должно идти через signed URL;
- нужна поддержка файлов сотрудников, оборудования и документов;
- нужен единый сервис загрузки и получения публичной ссылки;
- размер вложений должен быть ограничен;
- для документов должна поддерживаться file-role модель.

### 24.8 Требования к поиску и фильтрации

- списки должны поддерживать query-based filtering;
- нужна пагинация;
- нужна сортировка;
- для частых полей нужен debounce-поиск;
- фильтры должны отражать бизнес-статусы и контекст сущности.

### 24.9 Требования к архиву

- для документов должен быть поддержан архивный статус;
- для оборудования должен быть поддержан вывод из эксплуатации/списание;
- для справочников должен быть soft delete;
- сменные журналы нужно архивировать только если это подтвердит заказчик.

### 24.10 Требования к аудиту

- нужен журнал изменений как минимум для ключевых доменов;
- нужен журнал доставки уведомлений;
- нужны версии документов;
- для оборудования нужны журналы событий жизненного цикла.

### 24.11 Требования к API

- основной контур может оставаться Inertia/web-first;
- нужен минимальный формальный API только там, где он подтверждён;
- JSON-endpoints для модалок допустимы, если они соответствуют текущему паттерну;
- file-download endpoint должен поддерживать signed access.

### 24.12 Требования к импорту данных

- по старым отчётам подтверждён экспорт, но не подтверждён полноценный импорт;
- импорт данных следует считать `Недостаточно данных для точного вывода`;
- если импорт нужен, его нужно отдельно согласовать с заказчиком.

### 24.13 Требования к эксплуатации

- система должна поддерживать блокировку пользователя на уровне middleware;
- должна поддерживать очереди и мониторинг доставки;
- должна сохранять чёткую модель production/dev различий для очередей, кэша и файлов;
- для критичных workflows должны быть сервисные guards и понятные ошибки;
- должна быть поддержка ежедневных/периодических регламентных действий для смен.

## 25. Риски переноса логики из старого приложения

- риск переноса несогласованных статусов и дублирующих полей;
- риск переноса незавершённых TODO-веток как будто это готовый функционал;
- риск поломки жизненных циклов из-за timezone-смешения;
- риск переноса demo routes и demo exports как обязательных функций;
- риск ошибки при переносе очередей и JSON-endpoints;
- риск неполного понимания прав, которые есть только в seed-данных;
- риск утери доменной логики, которая живёт в сервисах, а не в схемах БД;
- риск переноса hardcoded operational-остатков;
- риск неправильной трактовки статусов equipment и documents без отдельной нормализации.

## 26. Критические уточнения перед проектированием

- подтвердить целевой набор ролей и permissions;
- подтвердить, какие статусы являются обязательными для новой версии;
- подтвердить, какие отчёты считаются бизнес-обязательными, а какие являются демо;
- подтвердить, нужен ли перенос всех сменных журналов;
- подтвердить, как трактовать файлы и архив;
- подтвердить политику по timezone-aware полям;
- подтвердить, что делать с незавершёнными ветками `UserController`, `PositionService`, `QueueController`, demo exports;
- подтвердить, нужен ли отдельный row-level access scope по филиалу/объекту/исполнителю.

## 27. Выводы для дальнейшей разработки

Старая версия приложения уже содержит достаточно зрелый набор доменов, чтобы использовать её как рабочую основу для новой системы. Особенно ценны:
- модульная структура;
- RBAC-модель;
- кадровый контур;
- оборудование с жизненным циклом;
- документы с версиями и файлами;
- сменные workflows;
- журналы и отчёты;
- приватное хранение файлов;
- service-layer подход к бизнес-логике.

При этом нельзя переносить старую систему механически. Перед проектированием новой версии нужно отдельно подтвердить:
- роли и права;
- спорные статусы;
- архивные сценарии;
- поведение очередей;
- demo-export функциональность;
- timezone-политику;
- список обязательных документов и отчётов.
