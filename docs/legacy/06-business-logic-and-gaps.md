# Бизнес-логика, незавершенные части и расхождения

## 1. Назначение отчета

Этот отчет фиксирует бизнес-логику старой версии приложения, которая уже реализована в коде или читается из него частично. Цель - отделить подтвержденные правила от логических выводов и предположений, а также собрать незавершенные части, заглушки и места, где код, документация, UI и БД могут расходиться.

Легенда:

- `Факт` - правило подтверждено кодом или тестом напрямую.
- `Логический вывод` - правило восстановлено из нескольких связанных источников.
- `Предположение` - данных недостаточно, чтобы утверждать правило как факт.

## 2. Изученные источники

Основные группы источников:

- Роуты: `routes/web.php`, `routes/api.php`
- Провайдеры и middleware: `app/Providers/AuthServiceProvider.php`, `app/Http/Middleware/CheckUserStatus.php`, `bootstrap/app.php`, `bootstrap/providers.php`
- Контроллеры и сервисы Core: `app/Modules/Core/Http/Controllers/*`, `app/Modules/Core/Services/*`, `app/Models/User.php`
- Сервисы и контроллеры смен: `app/Modules/Shifts/Http/Controllers/*`, `app/Modules/Shifts/Services/*`, `app/Modules/Shifts/Models/*`
- Сервисы и правила по оборудованию: `app/Modules/Equipment/Http/Requests/*`, `app/Modules/Equipment/Services/*`
- Документы и файлы: `app/Modules/Documents/Http/Requests/*`, `app/Modules/Documents/Services/DocumentService.php`, `app/Services/FileStorageService.php`, `app/Http/Controllers/FileDownloadController.php`
- Seeders и RBAC: `database/seeders/DatabaseSeeder.php`, `database/seeders/PermissionsSeeder.php`, `database/seeders/LaborantShiftPermissionsSeeder.php`, `database/seeders/DecoderShiftPermissionsSeeder.php`
- Политики: `app/Modules/Documents/Policies/DocumentPolicy.php`, `app/Modules/Shifts/Policies/ShiftPolicy.php`, `app/Providers/AuthServiceProvider.php`
- Тесты: `tests/Feature/Auth/UserStatusCheckTest.php`, `tests/Feature/Documents/DocumentIndexAuthorizationTest.php`, `tests/Feature/Core/ShiftFinishScenarioTest.php`
- Документация и исследовательские заметки: `docs/entities/*.md`, `docs/testing/*.md`, `docs/legacy-app-research/*.md`

## 3. Найденная бизнес-логика

- `Факт.` Пользователь со статусом `blocked` не может оставаться в сессии: middleware принудительно разлогинивает его, инвалидирует сессию и отправляет на логин. Источники: `app/Http/Middleware/CheckUserStatus.php:19-39`, `app/Models/User.php:75-86`, `tests/Feature/Auth/UserStatusCheckTest.php:63-87`.
- `Факт.` Администратор получает bypass всех проверок через `Gate::before`, если у пользователя роль `admin`. Источник: `app/Providers/AuthServiceProvider.php:107-114`.
- `Факт.` Права доступа в основном строятся на permission-строках, а политики почти везде сводятся к `can('permission')`. Источники: `app/Providers/AuthServiceProvider.php:73-102`, `app/Modules/Shifts/Policies/ShiftPolicy.php:15-103`.
- `Факт.` Страница документов открывается не только по `document.view_any`, но и по `document.view`. Источники: `app/Modules/Documents/Policies/DocumentPolicy.php:15-24`, `tests/Feature/Documents/DocumentIndexAuthorizationTest.php:16-53`.
- `Факт.` Страница очередей требует одновременно `queue.view_any` и `notification_delivery.view_any`, потому что оба middleware навешаны на один и тот же action. Источник: `app/Modules/Core/Http/Controllers/QueueController.php:14-23`.
- `Факт.` Смена привязана к пользователю через сотрудника, а не напрямую к аккаунту: `ShiftStateService` сначала ищет `Employee` по `user_id`, затем активную смену по `employee_id`. Источник: `app/Modules/Shifts/Services/ShiftStateService.php:14-47`.
- `Факт.` Начать смену можно только один раз в день на одного сотрудника. Источник: `app/Modules/Shifts/Services/ShiftStartService.php:20-48`.
- `Факт.` Завершение смены сценарно разделено на `lab` и `decoder`; для `decoder` обязательны журнал просмотренной пленки и уборка рабочего места. Источники: `app/Modules/Shifts/Services/AbstractShiftCompletionService.php:15-29`, `app/Modules/Shifts/Services/DecoderShiftCompletionService.php:29-77`, `tests/Feature/Core/ShiftFinishScenarioTest.php:26-97`.
- `Факт.` Для лабораторной смены завершение дополнительно сохраняет регламентные работы и запись `shifts_labassistant`. Источники: `app/Modules/Shifts/Services/LaborantShiftCompletionService.php:34-90`, `tests/Feature/Core/ShiftFinishScenarioTest.php:109-144`.
- `Факт.` Управление химией и пленкой завязано на текущую открытую смену и текущий объект, а не на произвольные записи. Источники: `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php:38-154`, `app/Modules/Shifts/Services/ShiftStateService.php:33-47`, `app/Modules/Shifts/Services/LaborantShiftService.php:169-202`.
- `Факт.` Списанное оборудование, оборудование в ремонте, оборудование без действующей поверки/калибровки и оборудование с критичным открытым дефектом нельзя выдавать. Источник: `app/Modules/Equipment/Services/EquipmentAssignmentService.php:120-248`.
- `Факт.` Документы версионируются: при создании `version_no = 1`, при обновлении добавляется новая версия и логируется смена статуса. Источник: `app/Modules/Documents/Services/DocumentService.php:82-165`.
- `Факт.` Файлы хранятся приватно, шифруются перед записью и раздаются через подписанный URL. Источники: `app/Services/FileStorageService.php:25-118`, `app/Http/Controllers/FileDownloadController.php:16-56`.
- `Логический вывод.` Система ориентирована на строгий RBAC-подход с доменными permissions на сущность и действие, а не на проверку ролей напрямую. Источники: `database/seeders/PermissionsSeeder.php:21-224`, `database/seeders/LaborantShiftPermissionsSeeder.php:13-148`, `database/seeders/DecoderShiftPermissionsSeeder.php:13-69`.

## 4. Правила создания сущностей

- `Факт.` Пользователи создаются с полями `name`, `email`, `password`, `status`, плюс роли назначаются отдельно. Источники: `routes/web.php:52-59`, `app/Modules/Core/Http/Requests/CreateUserRequest.php`, `app/Modules/Core/Http/Controllers/UserController.php:103-113`.
- `Факт.` При создании должности сервис сначала ищет удаленную запись с тем же `code`; если она есть, запись восстанавливается и обновляется, иначе создается новая. Источник: `app/Modules/Core/Services/PositionService.php:86-129`.
- `Факт.` Создание документа требует `document_type_id`, `title`, `status`, `is_confidential`, `requires_renewal`, а также проверяет взаимосвязь `owner_type` / `owner_id` и запрет самоссылок. Источники: `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:18-97`.
- `Факт.` При создании документа сразу устанавливается `version_no = 1`, сохраняются теги, связи и загруженные файлы, затем создается версия документа. Источник: `app/Modules/Documents/Services/DocumentService.php:82-111`.
- `Факт.` Создание оборудования требует уникальный `inventory_number`, обязательные `name`, `equipment_type_id`, `status`, `condition` и набор дат с ограничениями на последовательность. Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentRequest.php:17-114`.
- `Факт.` Создание выдачи оборудования требует `equipment_id`, `issued_at`, `status`, а также хотя бы `employee_id` или `branch_id`. Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentAssignmentRequest.php:17-55`.
- `Факт.` Создание поверки и калибровки проверяет интервальные даты и типы процедур, а также согласованность `valid_from` / `valid_until` и `performed_at` / `next_*`. Источники: `app/Modules/Equipment/Http/Requests/StoreEquipmentVerificationRequest.php:17-53`, `app/Modules/Equipment/Http/Requests/StoreEquipmentCalibrationRequest.php:17-54`.
- `Факт.` Создание перемещения оборудования запрещает одинаковые `from_branch_id` и `to_branch_id`. Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentMovementRequest.php:17-42`.
- `Факт.` Для смены лаборанта и расшифровщика создаются разные ветки поведения, но обе опираются на один и тот же базовый факт: смена создается для открытой смены текущего сотрудника. Источники: `app/Modules/Shifts/Services/ShiftStartService.php:20-48`, `app/Modules/Shifts/Services/LaborantShiftService.php:169-202`.
- `Факт.` Для учета уведомлений по химии создаются запросы со статусом `pending`, а затем они могут быть автоматически закрыты при поступлении химии. Источник: `app/Modules/Shifts/Services/DevelopingChemicalService.php:90-184`, `app/Modules/Shifts/Services/DevelopingChemicalService.php:595-614`.

## 5. Правила изменения сущностей

- `Факт.` Пользователь редактируется через отдельный DTO и сервис; пароль на обновлении может быть необязательным. Источники: `app/Modules/Core/Http/Controllers/UserController.php:53-63`, `app/Modules/Core/Http/Requests/UpdateUserRequest.php`.
- `Факт.` Должности можно активировать/деактивировать и удалять, но проверки зависимости от пользователей сейчас отключены закомментированными TODO-блоками. Источник: `app/Modules/Core/Services/PositionService.php:159-216`.
- `Факт.` Обновление документа создает новую версию, обновляет связи/теги/файлы, логирует общее обновление и отдельно логирует смену статуса, если статус изменился. Источник: `app/Modules/Documents/Services/DocumentService.php:114-165`.
- `Факт.` Удаление документа - soft delete с записью `deleted_by`. Источник: `app/Modules/Documents/Services/DocumentService.php:167-180`.
- `Факт.` Обновление оборудования и его связанных сущностей не просто меняет карточку, а также пересчитывает агрегированные поля по поверке, калибровке, ремонту и перемещению. Источники: `app/Modules/Equipment/Services/EquipmentVerificationService.php:125-249`, `app/Modules/Equipment/Services/EquipmentCalibrationService.php:125-258`, `app/Modules/Equipment/Services/EquipmentMaintenanceService.php:124-225`, `app/Modules/Equipment/Services/EquipmentMovementService.php:133-220`, `app/Modules/Equipment/Services/EquipmentDefectService.php:123-186`.
- `Факт.` У оборудования обновление дефекта может менять доступность всей единицы: при наличии открытого дефекта с `impact_on_operation = operation_prohibited` оборудование становится неактивным. Источник: `app/Modules/Equipment/Services/EquipmentDefectService.php:165-186`.
- `Факт.` Обновление поверки и калибровки запрещает существование второй актуальной `passed`-записи на ту же единицу, если прежняя еще действует. Источники: `app/Modules/Equipment/Services/EquipmentVerificationService.php:166-192`, `app/Modules/Equipment/Services/EquipmentCalibrationService.php:166-192`.
- `Факт.` Обновление выдачи оборудования сохраняет только одну активную запись выдачи на единицу оборудования. Источник: `app/Modules/Equipment/Services/EquipmentAssignmentService.php:191-208`.
- `Факт.` Для смены при завершении вендор-логика не в `controller`, а в workflow-сервисах: controller только запускает сценарий через `ShiftFinisher`. Источники: `app/Modules/Shifts/Http/Controllers/ShiftController.php:47-69`, `app/Modules/Shifts/Services/ShiftFinisher.php:19-31`.

## 6. Правила назначения и выполнения работ

- `Факт.` Лаборант может открыть смену только если у пользователя есть `shift.start`, есть связанный `Employee`, и смена на сегодня еще не стартовала. Источники: `app/Modules/Shifts/Services/ShiftStartService.php:20-48`, `app/Modules/Shifts/Services/LaborantShiftService.php:28-81`.
- `Факт.` Смена завершается только при наличии открытой смены и разрешающего permissions `shift.finish`. Источники: `app/Modules/Shifts/Services/AbstractShiftCompletionService.php:15-47`, `app/Modules/Shifts/Http/Controllers/ShiftController.php:47-69`.
- `Факт.` Для `decoder` завершение смены невозможно без журнала просмотренной пленки и подтвержденной уборки. Источник: `app/Modules/Shifts/Services/DecoderShiftCompletionService.php:29-77`.
- `Факт.` Для `lab` сценария завершения сохраняются данные регламентных работ по проявочной машине и формируется запись `ShiftLabassistant`. Источник: `app/Modules/Shifts/Services/LaborantShiftCompletionService.php:34-90`.
- `Факт.` Регламентные работы проявочной машины делятся на ежедневные до старта, ежедневные после завершения, еженедельные и ежемесячные блоки. Источник: `app/Modules/Shifts/Services/DevelopingMachineMaintenanceService.php:25-53`.
- `Факт.` Для возможности завершить смену лаборанта обязательны выполненные ежедневные start/end-пункты, а при необходимости - weekly/monthly. Источник: `app/Modules/Shifts/Services/DevelopingMachineMaintenanceService.php:269-291`.
- `Факт.` Еженедельная регламентная работа считается обязательной, если с последнего выполнения прошло 6 и более дней; ежемесячная - если прошло 29 и более дней. Источник: `app/Modules/Shifts/Services/DevelopingMachineMaintenanceService.php:294-312`.
- `Факт.` Выдача пленки сотруднику разрешена только сотруднику текущего объекта через филиал текущего объекта. Источник: `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php:110-132`.
- `Факт.` Замена химии на проявочной машине разрешена только на машине текущего объекта, причем машина должна быть именно типа `Проявочная машина`. Источник: `app/Modules/Shifts/Services/LaborantChemicalReplacementService.php:45-61`.
- `Факт.` При замене химии автоматически отправляется уведомление ответственному сотруднику объекта, если у него есть email. Источник: `app/Modules/Shifts/Services/LaborantChemicalReplacementService.php:63-123`.
- `Факт.` При поступлении химии система автоматически закрывает ближайший pending-запрос того же типа на том же объекте. Источник: `app/Modules/Shifts/Services/DevelopingChemicalService.php:595-614`.
- `Факт.` Для выдачи пленки проверяется достаточный остаток на объекте до создания исходящей транзакции. Источник: `app/Modules/Shifts/Services/FilmInventoryService.php:218-247`.
- `Факт.` Для запроса пленки и химии требуется хотя бы одна позиция в заявке, иначе выбрасывается validation error. Источники: `app/Modules/Shifts/Services/FilmInventoryService.php:124-215`, `app/Modules/Shifts/Services/DevelopingChemicalService.php:90-184`.

## 7. Правила заполнения отчетов и документов

- `Факт.` Документ поддерживает статусы `draft`, `active`, `expired`, `terminated`, `archived`, `superseded`, `cancelled`. Источники: `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:20-60`, `app/Modules/Documents/Services/DocumentService.php:192-232`.
- `Факт.` Для статуса `superseded` обязательно указать `superseded_by_document_id`. Источник: `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:63-70`.
- `Факт.` Документ может принадлежать `organization`, `branch` или `employee`, и эти поля должны быть согласованы как пара `owner_type` / `owner_id`. Источник: `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:35-89`.
- `Факт.` Самоссылки документа запрещены как для владельца/родителя, так и для связей `related_document_id`. Источники: `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:91-96`, `app/Modules/Documents/Http/Requests/UpdateDocumentRequest.php:15-31`.
- `Факт.` Для документа поддерживаются файлы с ролями `primary`, `attachment`, `scan`, `signed_copy`, `generated`. Источники: `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:56-60`, `app/Modules/Documents/Services/DocumentService.php:211-232`.
- `Факт.` Файл документа сохраняется через `FileStorageService` с `prefix = documents` и `contentDisposition = inline`. Источник: `app/Modules/Documents/Services/DocumentService.php:235-270`.
- `Факт.` Отчетные страницы по сменам есть как минимум в двух ветках: `lab-shift-reports` и `decoder-shift-reports`, а также общая страница `my-reports`. Источник: `routes/web.php:156-160`.
- `Логический вывод.` Структура отчетности строится вокруг смены, объекта, сотрудника и workflow, а не вокруг универсального отчета. Источники: `routes/web.php:156-170`, `app/Modules/Shifts/Services/LaborantShiftService.php:61-81`, `app/Modules/Shifts/Services/DecoderShiftService.php` (по связанным маршрутами и сервисам).
- `Предположение.` Полный набор правил формирования печатных/экспортных отчетов по сменам в старой версии еще нужно сверить с самими шаблонами и экспортерами, потому что текущий срез показывает маршруты и сервисы, но не полный набор шаблонов. Недостаточно данных для точного вывода.

## 8. Правила смены статусов

- `Факт.` `User.status` поддерживает как минимум `active` и `blocked`. Источники: `app/Models/User.php:25-86`, `tests/Feature/Auth/UserStatusCheckTest.php:16-87`.
- `Факт.` Смена после завершения переводится в `closed`, а `ended_at` пишется в UTC. Источники: `app/Modules/Shifts/Services/AbstractShiftCompletionService.php:20-29`, `app/Modules/Shifts/Services/LaborantShiftService.php:109-157`.
- `Факт.` У документа есть собственный жизненный цикл статуса, и его изменение логируется отдельным событием `status_changed`. Источник: `app/Modules/Documents/Services/DocumentService.php:147-161`.
- `Факт.` У оборудования статус меняется через разные подсистемы: выдача, перемещение, ремонт, дефекты, поверка и калибровка. Источники: `app/Modules/Equipment/Services/EquipmentAssignmentService.php:210-248`, `app/Modules/Equipment/Services/EquipmentMovementService.php:179-220`, `app/Modules/Equipment/Services/EquipmentMaintenanceService.php:160-225`, `app/Modules/Equipment/Services/EquipmentDefectService.php:165-186`, `app/Modules/Equipment/Services/EquipmentVerificationService.php:194-249`, `app/Modules/Equipment/Services/EquipmentCalibrationService.php:194-258`.
- `Факт.` Статус химического запроса переключается с `pending` на `completed` при поступлении соответствующей химии; для списков также существует `cancelled`. Источники: `app/Modules/Shifts/Services/DevelopingChemicalService.php:595-614`, `app/Modules/Shifts/Enums/ChemicalRequestStatus.php`, `app/Modules/Shifts/Models/ChemicalRequest.php`.
- `Факт.` У должности есть флаг активности `is_active`, который можно переключать отдельно от soft delete. Источники: `app/Modules/Core/Services/PositionService.php:159-193`, `app/Modules/Core/Services/PositionService.php:195-235`.
- `Факт.` Для дефектов оборудования открытые статусы и закрытые статусы непосредственно влияют на `is_active` у оборудования. Источник: `app/Modules/Equipment/Services/EquipmentDefectService.php:173-186`.

## 9. Правила доступа

- `Факт.` Проверка доступа строится через permissions и policies, а не через прямую проверку ролей, кроме `admin`-bypass и отдельных исключений. Источники: `app/Providers/AuthServiceProvider.php:73-114`, `app/Modules/Shifts/Policies/ShiftPolicy.php:15-103`.
- `Факт.` Для смен расшифровщика `replaceChemical` требует одновременно роль `lab` и permission `shift.replace_chemical`. Источник: `app/Modules/Shifts/Policies/ShiftPolicy.php:50-53`.
- `Факт.` Для документов индекс доступен, если есть `document.view_any` или `document.view`. Источники: `app/Modules/Documents/Policies/DocumentPolicy.php:15-24`, `tests/Feature/Documents/DocumentIndexAuthorizationTest.php:16-53`.
- `Факт.` Для страницы очередей нужны одновременно `queue.view_any` и `notification_delivery.view_any`. Источник: `app/Modules/Core/Http/Controllers/QueueController.php:14-23`.
- `Факт.` Контроллер пользователей сам вызывает `authorizeResource`, а также проверяет `viewAny`, `create`, `update`, `delete` на уровне экшенов. Источник: `app/Modules/Core/Http/Controllers/UserController.php:20-113`.
- `Факт.` Неавторизованные действия по смене блокируются и на уровне контроллера, и на уровне сервиса. Источники: `app/Modules/Shifts/Http/Controllers/ShiftController.php:34-121`, `app/Modules/Shifts/Services/AbstractShiftCompletionService.php:32-48`.
- `Факт.` Пользователь с заблокированным статусом не должен проходить даже на уже существующей сессии; это отдельный слой доступа, независимый от permissions. Источник: `app/Http/Middleware/CheckUserStatus.php:19-39`.
- `Логический вывод.` Меню и доступ к разделам, вероятно, строятся на permission-матрице из seeders, потому что все основные роли синхронизируются через `syncPermissions`. Источники: `database/seeders/PermissionsSeeder.php:207-223`, `database/seeders/LaborantShiftPermissionsSeeder.php:120-147`, `database/seeders/DecoderShiftPermissionsSeeder.php:40-68`.

## 10. Правила работы с файлами

- `Факт.` Загрузка файла идет через `FileStorageService`, который читает содержимое, шифрует base64-данные и сохраняет файл как private. Источник: `app/Services/FileStorageService.php:25-45`.
- `Факт.` Публичный доступ к файлу происходит только по временному подписанному URL с token. Источник: `app/Services/FileStorageService.php:57-64`.
- `Факт.` Для скачивания проверяются и срок действия, и подпись URL; при ошибке расшифровки или восстановления токена контроллер возвращает 404/410/403 в зависимости от ситуации. Источник: `app/Http/Controllers/FileDownloadController.php:16-56`.
- `Факт.` `DocumentService` сохраняет метаданные загруженного файла в `file_storage_path` как JSON, а удаление файла сначала удаляет физический объект, потом запись. Источники: `app/Modules/Documents/Services/DocumentService.php:235-306`.
- `Факт.` Уведомления и документы используют приватное хранение, а не прямую публикацию в storage URL; это подтверждается и тестом на signed URL. Источник: `tests/Unit/Modules/Equipment/DTO/EquipmentDocumentDataTest.php` и `app/Services/FileStorageService.php:57-118`.
- `Логический вывод.` Файл-логика написана так, чтобы одна и та же сущность могла использоваться в нескольких доменных местах, не раскрывая физический путь пользователю. Источники: `app/Services/FileStorageService.php:25-118`, `app/Http/Controllers/FileDownloadController.php:16-44`, `app/Modules/Documents/Services/DocumentService.php:235-306`.

## 11. Автоматические действия и расчеты

- `Факт.` Dashboard считает сроки медосмотров, инструктажей, квалификаций, СИЗ и выводит ближайшие/просроченные события. Источник: `app/Modules/Core/Services/DashboardService.php:72-247`.
- `Факт.` Dashboard считает последние уведомления, статус обучения и текущие film issues по открытой смене. Источник: `app/Modules/Core/Services/DashboardService.php:149-290`.
- `Факт.` Для film issue по текущей смене сумма считается по `issued_to_employee_id` и `film_type_id`. Источник: `app/Modules/Core/Services/DashboardService.php:253-290`.
- `Факт.` При проверке/калибровке агрегированные поля оборудования синхронизируются автоматически: статус, даты последней процедуры, диапазоны действия, next_* поля и, для калибровки, возможность выдачи в проект. Источники: `app/Modules/Equipment/Services/EquipmentVerificationService.php:194-249`, `app/Modules/Equipment/Services/EquipmentCalibrationService.php:194-258`.
- `Факт.` После ремонта оборудование может получить обновленную дату следующей поверки/калибровки - на текущий день, если эти проверки обязательны. Источник: `app/Modules/Equipment/Services/EquipmentMaintenanceService.php:198-225`.
- `Факт.` После завершения перемещения оборудования его филиал и статус обновляются в зависимости от последнего completed movement. Источник: `app/Modules/Equipment/Services/EquipmentMovementService.php:179-220`.
- `Факт.` Если есть открытый критичный дефект, оборудование помечается как недоступное через `is_active = false`; если дефект закрыт, активность возвращается. Источник: `app/Modules/Equipment/Services/EquipmentDefectService.php:173-186`.
- `Факт.` `NotificationService` выбирает каналы по флагам сотрудника и может принудительно включать каналы через `forceChannels`; также есть дедупликация по `dedupKey`. Источник: `app/Services/NotificationService.php:28-114`.
- `Факт.` На уровне смены есть авто-списания химии при завершении смены: `registerReplacementOnShiftFinish()` создает по одной операции на каждый тип химии, если остатка достаточно. Источник: `app/Modules/Shifts/Services/DevelopingChemicalService.php:298-340`.
- `Факт.` Сброс смен в dev/не production удаляет все связанные сменные данные и пишет warning в лог. Источник: `app/Modules/Shifts/Services/ShiftResetService.php:29-91`.
- `Логический вывод.` Часть автоматизации в проекте реализована как пересчет агрегатов в доменных сервисах, а не через фоновые jobs. Это видно по синхронизации полей сразу после CRUD-операций. Источники: `app/Modules/Equipment/Services/EquipmentVerificationService.php:194-249`, `app/Modules/Equipment/Services/EquipmentCalibrationService.php:194-258`, `app/Modules/Equipment/Services/EquipmentMaintenanceService.php:160-225`, `app/Modules/Equipment/Services/EquipmentMovementService.php:170-220`, `app/Modules/Equipment/Services/EquipmentDefectService.php:165-186`, `app/Modules/Documents/Services/DocumentService.php:82-165`, `app/Modules/Shifts/Services/DevelopingChemicalService.php:38-87`.

## 12. Исключительные ситуации

- `Факт.` Попытка начать смену без связанного сотрудника, без права `shift.start` или при уже начатой смене вызывает `RuntimeException`. Источник: `app/Modules/Shifts/Services/ShiftStartService.php:20-48`.
- `Факт.` Попытка завершить смену без открытой смены, без права `shift.finish` или при неподходящем workflow также вызывает исключение. Источник: `app/Modules/Shifts/Services/AbstractShiftCompletionService.php:15-47`.
- `Факт.` Для decoder-смены отсутствие film groups или cleanup приводит к отказу в завершении смены. Источник: `app/Modules/Shifts/Services/DecoderShiftCompletionService.php:49-69`.
- `Факт.` Для maintenance state и finish state сервисы могут вернуть не только state, но и сообщение ошибки, если смена еще не готова к закрытию. Источник: `app/Modules/Shifts/Http/Controllers/ShiftController.php:71-90`.
- `Факт.` Операции с оборудованием выбрасывают `DomainException` при нарушении правил доступности, повторной актуальной поверке/калибровке, дублях активной выдачи и т.д. Источники: `app/Modules/Equipment/Services/EquipmentAssignmentService.php:191-248`, `app/Modules/Equipment/Services/EquipmentVerificationService.php:166-192`, `app/Modules/Equipment/Services/EquipmentCalibrationService.php:166-192`.
- `Факт.` Для файлов предусмотрены отдельные коды ошибок: 410 при истекшей ссылке, 403 при невалидной подписи, 404 при невозможности расшифровать или найти файл. Источник: `app/Http/Controllers/FileDownloadController.php:16-44`.
- `Факт.` Блокировка пользователя срабатывает и на входе, и на следующем запросе после смены статуса на `blocked`. Источник: `tests/Feature/Auth/UserStatusCheckTest.php:16-87`.
- `Факт.` В `DocumentService::deleteFile()` файл не удаляется, если он не принадлежит документу; метод просто выходит. Источник: `app/Modules/Documents/Services/DocumentService.php:273-306`.
- `Факт.` При попытке использовать недоступный тип пленки или химии в запросах/выдаче сервисы кидают `ValidationException`. Источники: `app/Modules/Shifts/Services/FilmInventoryService.php:124-215`, `app/Modules/Shifts/Services/DevelopingChemicalService.php:90-184`.
- `Предположение.` Некоторые исключения в старой версии были намеренно превращены в мягкие `back()->with('error')`, а не в HTTP 4xx, чтобы не ломать пользовательский сценарий. Это видно в контроллерах смен и очередей, но для всех разделов нужно отдельное UX-подтверждение.

## 13. Незавершенные части проекта

- `Факт.` `UserController` содержит незавершенные методы `show`, `profile`, `updateProfile`, `changePassword`, которые только помечены TODO. Источник: `app/Modules/Core/Http/Controllers/UserController.php:115-145`.
- `Факт.` `PositionService` отключил проверки зависимостей от пользователей при toggle/delete через закомментированные TODO-блоки. Источник: `app/Modules/Core/Services/PositionService.php:164-216`.
- `Факт.` `PositionListItemData` держит `users_count` и `active_users_count` в `null`, потому что связь с пользователями еще не реализована. Источник: `app/Modules/Core/DTO/PositionListItemData.php:29-45`.
- `Факт.` `app/Modules/Shifts/Providers/ShiftsServiceProvider.php` пустой, никаких boot-регистраций не выполняет. Источник: `app/Modules/Shifts/Providers/ShiftsServiceProvider.php:9-15`.
- `Факт.` В `FilmInventoryService::createReceipt()` блок email-уведомления закомментирован. Источник: `app/Modules/Shifts/Services/FilmInventoryService.php:93-119`.
- `Факт.` В `routes/web.php` root и профиль ведут в `DashboardController::index`, а отдельные методы профиля пользователя из `UserController` не подключены. Источники: `routes/web.php:39-49`, `app/Modules/Core/Http/Controllers/UserController.php:115-145`.
- `Логический вывод.` Не все отчетные и UI-ветки доведены до одной модели данных: часть функций живет в сервисах и тестах, а часть - только как маршруты или TODO. Это видно по `UserController`, `PositionService`, `ShiftsServiceProvider` и комментарию в `FilmInventoryService`.

## 14. TODO, FIXME и заглушки

- `TODO.` `app/Modules/Core/Http/Controllers/UserController.php:118-145` - методы профиля и показа пользователя не реализованы.
- `TODO.` `app/Modules/Core/Services/PositionService.php:170-207` - проверки активных/связанных пользователей отключены.
- `TODO.` `app/Modules/Core/DTO/PositionListItemData.php:29-43` - подсчет пользователей отложен до появления связи.
- `Заглушка.` `app/Modules/Shifts/Providers/ShiftsServiceProvider.php:9-15` - пустой провайдер.
- `Отключенный блок.` `app/Modules/Shifts/Services/FilmInventoryService.php:93-119` - закомментированная отправка email.
- `FIXME.` По текущему поиску явных `FIXME`-комментариев в исследованных файлах не обнаружено.

## 15. Неиспользуемый или мертвый код

- `Факт.` `app/Http/Controllers/IndexController.php:7-16` существует, но в `routes/web.php` на него нет маршрута; вместо него корень сайта ведет в `DashboardController::index`. Источники: `app/Http/Controllers/IndexController.php:7-16`, `routes/web.php:38-49`.
- `Факт.` Методы `show/profile/updateProfile/changePassword` в `UserController` не подключены к маршрутам и сейчас не используются. Источники: `app/Modules/Core/Http/Controllers/UserController.php:115-145`, `routes/web.php:52-59`.
- `Факт.` `ShiftsServiceProvider` не содержит логики и не влияет на поведение приложения. Источник: `app/Modules/Shifts/Providers/ShiftsServiceProvider.php:9-15`.
- `Предположение.` Есть вероятность, что часть тестовых и демонстрационных страниц из документации проекта является мертвым UX-остатком, но для точного вывода нужно отдельно сверить frontend-папки и реальные Inertia-компоненты.

## 16. Расхождения между документацией, БД, backend и frontend

- `Факт.` Документация и тестовые заметки уже фиксируют, что `users_count` / `active_users_count` в должностях пока пустые, и это совпадает с кодом DTO. Источники: `app/Modules/Core/DTO/PositionListItemData.php:29-45`, `docs/testing/Positions-test-plan.md:69,95`.
- `Факт.` В интерфейсе и маршрутах есть профильные маршруты, но реального profile CRUD в `UserController` нет - это явное расхождение между UI-ожиданием и backend-реализацией. Источники: `routes/web.php:42-49`, `app/Modules/Core/Http/Controllers/UserController.php:115-145`.
- `Факт.` Для документов код допускает вход на индекс по `document.view`, что подтверждено тестом; если в старой документации подразумевался только `document.view_any`, это уже функциональное расхождение. Источники: `app/Modules/Documents/Policies/DocumentPolicy.php:15-24`, `tests/Feature/Documents/DocumentIndexAuthorizationTest.php:16-53`.
- `Факт.` Очереди и deliveries в UI, судя по контроллеру, требуют сразу два permission-состояния. Если frontend/документация показывают только одно из них, это будет рассинхрон. Источник: `app/Modules/Core/Http/Controllers/QueueController.php:14-23`.
- `Факт.` Hardcoded email `v.kamelin@gmail.com` в `FilmInventoryService` выглядит как operational-остаток, а не как нормализованное доменное правило. Источник: `app/Modules/Shifts/Services/FilmInventoryService.php:28-33`.
- `Логический вывод.` Реальная модель домена в старом проекте жила не только в БД, но и в сервисах-агрегаторах, поэтому простое сравнение миграций и моделей недостаточно - нужно учитывать сервисные пересчеты и тестовые фиксации.

## 17. Что можно использовать в новой системе

- `Можно использовать.` Permission-матрицу и общую RBAC-схему с централизованным seeding и описаниями прав. Источники: `database/seeders/PermissionsSeeder.php:21-224`, `database/seeders/LaborantShiftPermissionsSeeder.php:13-148`, `database/seeders/DecoderShiftPermissionsSeeder.php:13-69`.
- `Можно использовать.` Подход к blocked-user middleware и принудительному logout как отдельному security layer. Источники: `app/Http/Middleware/CheckUserStatus.php:19-39`, `tests/Feature/Auth/UserStatusCheckTest.php:16-87`.
- `Можно использовать.` Workflow-разделение смен на `lab` и `decoder` с отдельными правилами завершения. Источники: `app/Modules/Shifts/Services/DecoderShiftCompletionService.php:29-77`, `app/Modules/Shifts/Services/LaborantShiftCompletionService.php:34-90`.
- `Можно использовать.` Приватное хранение файлов с signed URL на отдачу. Источники: `app/Services/FileStorageService.php:25-118`, `app/Http/Controllers/FileDownloadController.php:16-44`.
- `Можно использовать.` Автоматический пересчет агрегированных полей у оборудования после CRUD-операций. Источники: `app/Modules/Equipment/Services/EquipmentVerificationService.php:194-249`, `app/Modules/Equipment/Services/EquipmentCalibrationService.php:194-258`, `app/Modules/Equipment/Services/EquipmentMaintenanceService.php:160-225`, `app/Modules/Equipment/Services/EquipmentMovementService.php:170-220`, `app/Modules/Equipment/Services/EquipmentDefectService.php:165-186`.
- `Можно использовать.` Валидацию взаимосвязанных дат, статусов и self-link запретов в FormRequest-классах. Источники: `app/Modules/Equipment/Http/Requests/StoreEquipmentRequest.php:17-114`, `app/Modules/Equipment/Http/Requests/StoreEquipmentAssignmentRequest.php:17-55`, `app/Modules/Equipment/Http/Requests/StoreEquipmentMovementRequest.php:17-42`, `app/Modules/Equipment/Http/Requests/StoreEquipmentVerificationRequest.php:17-53`, `app/Modules/Equipment/Http/Requests/StoreEquipmentCalibrationRequest.php:17-54`, `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:18-97`, `app/Modules/Documents/Http/Requests/UpdateDocumentRequest.php:11-33`.
- `Можно использовать.` Обновление документа через версионирование и логирование `status_changed`. Источник: `app/Modules/Documents/Services/DocumentService.php:114-165`.

## 18. Что нельзя переносить без проверки

- `Нельзя переносить без проверки.` Hardcoded email `v.kamelin@gmail.com` для уведомлений по пленке. Источник: `app/Modules/Shifts/Services/FilmInventoryService.php:28-33`.
- `Нельзя переносить без проверки.` Закомментированные проверки зависимостей у должностей. Источник: `app/Modules/Core/Services/PositionService.php:170-207`.
- `Нельзя переносить без проверки.` Пустой `ShiftsServiceProvider` как будто он что-то инициализирует. Источник: `app/Modules/Shifts/Providers/ShiftsServiceProvider.php:9-15`.
- `Нельзя переносить без проверки.` Отдельный `IndexController`, потому что он сейчас не участвует в маршрутизации. Источники: `app/Http/Controllers/IndexController.php:7-16`, `routes/web.php:38-49`.
- `Нельзя переносить без проверки.` Текущую логику очередей с двойным middleware без анализа UX, потому что она может оказаться слишком жесткой. Источник: `app/Modules/Core/Http/Controllers/QueueController.php:14-23`.
- `Нельзя переносить без проверки.` Статусные цепочки оборудования и документов, если в новой БД будут иные enum-значения или семантика дат. Источники: `app/Modules/Equipment/Http/Requests/StoreEquipmentRequest.php:17-114`, `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php:18-60`.
- `Нельзя переносить без проверки.` Логику авто-закрытия chemical request при receipt, если заказчик хочет другое правило учета остатков. Источник: `app/Modules/Shifts/Services/DevelopingChemicalService.php:595-614`.