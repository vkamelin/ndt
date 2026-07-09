# Общая инвентаризация старого проекта

## 1. Назначение отчета

Этот отчет фиксирует фактическое состояние старой версии приложения: структуру каталогов, стек, архитектуру, основные модули, точки входа, миграции, тесты, сидеры, служебные команды и заметные незавершенные или экспериментальные части.

Главный источник истины здесь - реальный код. Документация из `docs/` использовалась только как дополнительная проверка.

## 2. Изученные источники

Основные источники кода:

| Категория | Источники |
|---|---|
| Точки входа и маршруты | `bootstrap/app.php`, `bootstrap/providers.php`, `routes/web.php`, `routes/api.php`, `routes/console.php` |
| Конфиг и стек | `composer.json`, `package.json`, `config/app.php`, `config/auth.php`, `config/database.php`, `config/cache.php`, `config/queue.php`, `config/services.php`, `config/filesystems.php`, `config/file_storage.php`, `config/permission.php`, `config/pdf_export.php`, `config/sanctum.php`, `config/mail.php`, `config/notifications.php` |
| Backend ядро | `app/Providers/*`, `app/Http/*`, `app/Models/*`, `app/Services/*` |
| Доменные модули | `app/Modules/Core/*`, `app/Modules/Organizations/*`, `app/Modules/Equipment/*`, `app/Modules/Documents/*`, `app/Modules/Shifts/*` |
| Frontend | `resources/js/app.ts`, `resources/js/Layout.vue`, `resources/js/modules/*`, `resources/js/components/*`, `resources/views/*`, `resources/css/app.css` |
| База данных | `database/migrations/*`, `database/seeders/*` |
| Тесты | `tests/Feature/*`, `tests/Unit/*` |
| Доп. документация | `docs/Architecture.md`, `docs/ModuleStructure.md`, `docs/ProductScope.md`, `docs/FeatureMatrix.md`, `docs/UserRolesAndPermissions.md`, `docs/Entities.md`, `docs/entities/*.md`, `docs/ShiftWorkflow.md`, `docs/EXPORT_SERVICE.md`, `docs/PDF_EXPORT_SERVICE.md` |

## 3. Технологический стек

| Слой | Технологии |
|---|---|
| Backend | PHP 8.4, Laravel 12, Eloquent ORM, Inertia.js, Spatie Laravel Permission, Spatie Activitylog, Laravel Sanctum, Laravel Horizon, Laravel Octane, Maatwebsite Excel, Intervention Image, Barryvdh DomPDF, Spatie Laravel PDF |
| Frontend | Vue 3, Composition API, TypeScript, Inertia.js, Vite 7, Tabler UI, Tabler Icons, Tailwind CSS 4, Axios |
| Хранилище и инфраструктура | SQLite по умолчанию, PostgreSQL 16+ для production, Redis 7+ для production, database cache/queue/session для разработки |
| Тестирование | PHPUnit, feature/unit тесты в `tests/`, в скриптах есть команды `php artisan test`, `vendor/bin/phpunit`-уровня, а фронтенд-тестовый стек в `package.json` не выделен явно |
| Сборка и dev-скрипты | `composer scripts`, `npm scripts`, `php artisan` команды |

Ключевые подтверждения: `composer.json`, `package.json`, `config/database.php`, `config/cache.php`, `config/queue.php`, `config/services.php`, `config/permission.php`, `bootstrap/app.php`.

## 4. Общая структура директорий

| Директория | Назначение |
|---|---|
| `app/` | Backend-код Laravel, включая доменные модули, сервисы, middleware, провайдеры, jobs, commands, policies |
| `app/Modules/Core/` | Базовый домен: auth, dashboard, пользователи, роли, сотрудники, филиалы, объекты, должности, очереди |
| `app/Modules/Organizations/` | Домен организаций, контактов и банковских реквизитов |
| `app/Modules/Equipment/` | Домен оборудования и связанных журналов |
| `app/Modules/Documents/` | Домен документов, типов, файлов, версий и связей |
| `app/Modules/Shifts/` | Сменный workflow для лаборанта и расшифровщика |
| `app/Http/` | Кросс-модульные контроллеры и middleware |
| `app/Services/` | Общесистемные сервисы: файлы, PDF, экспорт, уведомления |
| `bootstrap/` | Регистрация провайдеров и bootstrap приложения |
| `config/` | Конфигурация Laravel и прикладных интеграций |
| `database/migrations/` | Схема БД |
| `database/seeders/` | Базовые данные и permissions |
| `docs/` | Документация проекта и доменных сущностей |
| `public/` | Публичные ассеты |
| `resources/js/` | Frontend Inertia/Vue |
| `resources/views/` | Blade root template, pdf/email templates и примеры экспорта |
| `routes/` | Основные маршруты web/api/console |
| `storage/` | Файловое хранилище и служебные данные |
| `tests/` | Unit и feature тесты |

Дополнительно на верхнем уровне есть служебные каталоги `.agents`, `.codex`, `.git`, `.idea`, `.vscode`, `node_modules`, `vendor`.

## 5. Основные модули приложения

| Модуль | Назначение | Источник |
|---|---|---|
| Core | Авторизация, dashboard, профиль, пользователи, роли, сотрудники, филиалы, объекты, должности, очереди | `app/Modules/Core/*`, `routes/web.php`, `resources/js/modules/Core/*` |
| Organizations | Организации, контакты, банковские реквизиты, печать и экспорт | `app/Modules/Organizations/*`, `app/Modules/Organizations/routes/web.php`, `resources/js/modules/Organizations/*` |
| Equipment | Оборудование, типы, поверки, калибровки, ТО, выдачи, перемещения, документы, дефекты | `app/Modules/Equipment/*`, `app/Modules/Equipment/routes/web.php`, `resources/js/modules/Equipment/*` |
| Documents | Документы, типы документов, версии, файлы, связи | `app/Modules/Documents/*`, `app/Modules/Documents/routes/web.php`, `resources/js/modules/Documents/*` |
| Shifts | Смены, журналы пленки и химии, отчеты лаборанта и расшифровщика | `app/Modules/Shifts/*`, `routes/web.php`, `resources/js/modules/Shifts/*` |

Вывод: по коду это модульный монолит, а не набор отдельных сервисов. Источник: `bootstrap/providers.php`, `app/Modules/*`, `resources/js/modules/*`, `routes/web.php`.

## 6. Backend

### Точки входа и bootstrap

- Приложение стартует через `bootstrap/app.php`.
- Web, API и console routing подключены из `routes/web.php`, `routes/api.php`, `routes/console.php`.
- В web middleware стек добавлены `HandleInertiaRequests` и `CheckUserStatus`.
- Service providers зарегистрированы в `bootstrap/providers.php`.

Источник: `bootstrap/app.php`, `bootstrap/providers.php`.

### HTTP-слой

- `app/Http/Controllers/FileDownloadController.php` отвечает за выдачу файлов по signed token.
- `app/Http/Controllers/ExportExampleController.php` содержит демонстрационные export/PDF endpoints.
- `app/Http/Controllers/IndexController.php` есть в коде, но в `routes/web.php` напрямую не используется.
- `app/Http/Middleware/HandleInertiaRequests.php` передает shared props: `auth.user`, `app.isProduction`, flash-сообщения.
- `app/Http/Middleware/CheckUserStatus.php` блокирует доступ заблокированным пользователям.

Источник: `app/Http/*`, `routes/web.php`, `bootstrap/app.php`.

### Доменные контроллеры

- Core: `AuthController`, `DashboardController`, `UserController`, `RoleController`, `EmployeeController`, `BranchController`, `ObjectController`, `PositionController`, `QueueController`.
- Organizations: `OrganizationController`, `OrganizationBankAccountController`, `OrganizationContactController`, `OrganizationsExportController`.
- Equipment: `EquipmentController`, `EquipmentTypeController`, `EquipmentVerificationController`, `EquipmentCalibrationController`, `EquipmentMaintenanceController`, `EquipmentAssignmentController`, `EquipmentMovementController`, `EquipmentDocumentController`, `EquipmentDefectController`, `EquipmentExportController`.
- Documents: `DocumentController`, `DocumentTypeController`.
- Shifts: `ShiftController`, `LaborantShiftController`, `DecoderShiftController`, `LabShiftReportController`, `DecoderShiftReportController`, `DecoderShiftFilmGroupController`, `DecoderShiftRejectController`, `DecoderShiftForgerySuspicionController`, `DecoderShiftDecryptionJournalController`, `FilmInventoryTransactionController`, `DevelopingChemicalTransactionController`, `ChemicalRequestController`, `MyReportController`.

Источник: `app/Modules/*/Http/Controllers/*`, `app/Modules/Organizations/Controllers/*`, `routes/web.php`.

### Сервисный слой

- Общесистемные сервисы: `app/Services/FileStorageService.php`, `app/Services/PdfExport/*`, `app/Services/Export/*`, `app/Services/Notification/*`, `app/Services/AuthService.php`, `app/Services/DataEncryptionService.php`.
- Модульные сервисы есть почти во всех доменах: `app/Modules/Core/Services/*`, `app/Modules/Organizations/Services/*`, `app/Modules/Equipment/Services/*`, `app/Modules/Documents/Services/*`, `app/Modules/Shifts/Services/*`.
- В Shifts заметен наиболее сложный orchestration: `ShiftStartService`, `ShiftFinisher`, `ShiftStateService`, `ShiftWorkflowResolver`, `ShiftCompletionServiceResolver`, workflow-specific start/finish services.

Источник: `app/Services/*`, `app/Modules/*/Services/*`.

### Модели, DTO, policies

- Модели находятся и в `app/Models`, и внутри модулей.
- Корневая модель `User` находится в `app/Models/User.php`; сущности доменов лежат в `app/Modules/*/Models`.
- DTO широко используются во всех доменах для create/update/list/show/export.
- Authorization построена на policies и Spatie Permission.

Источник: `app/Models/User.php`, `app/Modules/*/Models/*`, `app/Modules/*/DTO/*`, `app/Modules/*/Policies/*`, `config/permission.php`, `app/Providers/AuthServiceProvider.php`.

### Отдельно про кодовые хвосты backend

- В `app/Modules/Core/Http/Controllers/UserController.php` есть TODO-методы `show`, `profile`, `updateProfile`, `changePassword`.
- В `app/Modules/Core/Services/PositionService.php` есть TODO-комментарии по связи с пользователями.
- `app/Modules/Shifts/Providers/ShiftsServiceProvider.php` пустой; маршруты Shifts подключены через общий `routes/web.php`.

Источник: `app/Modules/Core/Http/Controllers/UserController.php`, `app/Modules/Core/Services/PositionService.php`, `app/Modules/Shifts/Providers/ShiftsServiceProvider.php`, `routes/web.php`.

## 7. Frontend

### Точка входа

- Главный frontend bootstrap - `resources/js/app.ts`.
- Root Blade-шаблон - `resources/views/app.blade.php`.
- `app.ts` резолвит страницы в формате `Module::Path/To/Page` и автоматически оборачивает их в глобальный и модульный layouts.

Источник: `resources/js/app.ts`, `resources/views/app.blade.php`.

### Layout-слой

- Глобальный layout: `resources/js/Layout.vue`.
- Модульные layout-файлы: `resources/js/modules/Core/Layout.vue`, `resources/js/modules/Organizations/Layout.vue`, `resources/js/modules/Equipment/Layout.vue`, `resources/js/modules/Documents/Layout.vue`, `resources/js/modules/Shifts/Layout.vue`.
- Sidebar и профильное меню живут в `resources/js/components/Sidebar.vue` и `resources/js/components/ProfileMenu.vue`.
- `resources/js/components/Can.vue` выполняет клиентскую проверку ролей/permissions.

Источник: `resources/js/Layout.vue`, `resources/js/modules/*/Layout.vue`, `resources/js/components/*`.

### Страницы и формы

- Страницы разложены по модулям в `resources/js/modules/*/Pages/*`.
- Формы чаще всего реализованы как modal-компоненты в `resources/js/modules/*/Components/*Modal.vue`.
- Documents использует отдельные страницы `Create.vue` и `Edit.vue`, а Core/Equipment/Organizations чаще работают через модалки.
- Общие UI-компоненты находятся в `resources/js/components/UI/*`.

Источник: `resources/js/modules/*/Pages/*`, `resources/js/modules/*/Components/*`, `resources/js/components/UI/*`.

### Composables и типы

- Общие composables: `resources/js/composables/useAuth.ts`, `useDebounce.ts`, `useToastr.ts`, `use*Filters.ts`.
- Модульные composables есть у Documents и других доменов.
- Типы вынесены в `resources/js/types/index.d.ts` и `resources/js/types/modules.d.ts`.

Источник: `resources/js/composables/*`, `resources/js/modules/Documents/Composables/*`, `resources/js/types/*`.

### Признаки зрелости UI

- Используются Tabler UI и Tabler Icons.
- Есть глобальный toastr-механизм.
- Есть permission-aware UI через `useAuth()` и `Can.vue`.
- Для многих экранов реализованы фильтры, сортировка, пагинация, модалки и отдельные show-страницы.

Источник: `resources/js/app.ts`, `resources/js/components/Can.vue`, `resources/js/components/TheToastr.vue`, `resources/js/modules/*`.

## 8. Админка

Отдельного маршрута вида `/admin` или отдельного админ-приложения в коде не найдено. Админская часть реализована внутри Core и частично в общем shell.

Что выглядит как админка:

- `/users`, `/roles`, `/employees`, `/branches`, `/objects`, `/positions`, `/queues` в `routes/web.php`.
- Верхняя навигация Core с разделами пользователей, ролей, должностей, сотрудников и объектов.
- Sidebar показывает админские разделы и зависит от permissions и роли `admin`.
- `AuthServiceProvider` содержит `Gate::before`, который дает роли `admin` полный bypass.

Источник: `routes/web.php`, `resources/js/modules/Core/Layout.vue`, `resources/js/components/Sidebar.vue`, `app/Providers/AuthServiceProvider.php`.

Вывод: административная зона есть, но она не выделена в отдельный backend/frontend контур. Это именно часть общего приложения. Источник: `routes/web.php`, `resources/js/app.ts`.

## 9. API

API в этой версии минимален.

| Endpoint | Назначение | Источник |
|---|---|---|
| `GET /api/user` | Возврат текущего пользователя под `auth:sanctum` | `routes/api.php` |

По коду не видно развитого REST API для доменных модулей. Большая часть взаимодействия идет через Inertia/web routes. Если нужен полный API-контур, недостаточно данных для точного вывода. Источник: `routes/api.php`, `routes/web.php`, `app/Modules/*`.

## 10. База данных и миграции

### Общая картина

- В `database/migrations` найдено 67 миграций.
- База по умолчанию - SQLite, но схема и миграции явно поддерживают PostgreSQL.
- В домене есть таблицы для пользователей, RBAC, сотрудников, филиалов, объектов, организаций, оборудования, документов, смен, уведомлений, job/cache и токенов.

Источник: `database/migrations/*`, `config/database.php`, `composer.json`.

### Ключевые доменные группы

| Группа | Примеры таблиц | Источник |
|---|---|---|
| Auth/RBAC | `users`, `roles`, `permissions`, pivot-таблицы Spatie | `database/migrations/0001_01_01_000000_create_users_table.php`, `2026_02_16_000000_create_permission_tables.php`, `2026_02_17_062056_add_soft_deletes_to_users_table.php`, `2026_02_20_000001_add_status_to_users_table.php` |
| Core HR | `employees`, `positions`, `branches`, `branch_phones`, `branch_emails`, `objects`, `objects_customers`, `employee_*` | `database/migrations/2026_02_23_*`, `2026_02_25_*`, `2026_03_03_*`, `2026_03_04_*`, `2026_03_16_*`, `2026_03_19_*` |
| Organizations | `organizations`, `organization_contacts`, `organization_bank_accounts`, `regions` | `database/migrations/2026_03_02_*`, `database/migrations/2026_03_01_*` |
| Equipment | `equipment_types`, `equipment`, `equipment_assignments`, `equipment_movements`, `equipment_verifications`, `equipment_calibrations`, `equipment_maintenances`, `equipment_defects`, `equipment_documents`, `equipment_*` | `database/migrations/2026_03_07_*`, `2026_03_08_*`, `2026_03_20_*` |
| Documents | `document_types`, `documents`, `document_files`, `document_versions`, `document_relations`, `document_tags`, `document_tag_assignments` | `database/migrations/2026_03_09_*` |
| Shifts | `shifts`, `shifts_labassistant`, `decoder_shift_*`, `film_inventory_transactions`, `developing_chemical_transactions`, `developing_machine_maintenance_logs`, `chemical_requests`, `rs_types`, `film_types` | `database/migrations/2026_03_12_*`, `2026_03_13_*`, `2026_03_16_*`, `2026_03_31_*` |
| Notifications / queues | `notification_deliveries`, `sms_messages`, `telegram_messages`, `max_messages`, `jobs`, `failed_jobs`, `cache`, `job_batches` | `database/migrations/0001_01_01_000001_create_cache_table.php`, `0001_01_01_000002_create_jobs_table.php`, `2026_03_04_*`, `2026_06_01_*` |

### Сидеры

- `DatabaseSeeder.php` создает базовых пользователей и роли `admin`, `lab`, `user`, а также вызывает сидеры permissions, positions, regions и film types.
- `PermissionsSeeder.php` создает основные permissions и синхронизирует их с ролью `admin`.
- `LaborantShiftPermissionsSeeder.php` и `DecoderShiftPermissionsSeeder.php` добавляют сменные permissions и назначают их `lab`/`decoder`.
- Есть также `RegionSeeder.php`, `PositionSeeder.php`, `FilmTypeSeeder.php`, `EquipmentPermissionsSeeder.php`, `DocumentPermissionsSeeder.php`.

Источник: `database/seeders/*`, особенно `DatabaseSeeder.php`, `PermissionsSeeder.php`, `LaborantShiftPermissionsSeeder.php`, `DecoderShiftPermissionsSeeder.php`.

### Важное наблюдение

По коду видно, что база уже не “пустая”: есть предметные миграции, soft delete, RBAC, журнальные таблицы, связи и отдельные таблицы для workflow. Это признак достаточно зрелой схемы. Источник: `database/migrations/*`, `database/seeders/*`.

## 11. Служебные команды, очереди, cron, workers

### Artisan commands

| Команда | Назначение | Источник |
|---|---|---|
| `notifications:scan` | Сканирование календарных уведомлений сотрудников | `app/Console/Commands/ScanNotificationsCommand.php` |
| `smsc:test-sms` | Тест SMS через SMSC | `app/Console/Commands/TestSmscSms.php` |
| `smsc:test-email` | Тест email через SMSC | `app/Console/Commands/TestSmscEmail.php` |
| `mail:test` | Тест отправки письма | `app/Console/Commands/TestMailCommand.php` |
| `inspire` | Стандартная демонстрационная команда Laravel | `routes/console.php` |

### Scheduler / cron

- В `routes/console.php` запланирован `notifications:scan` на каждый день в `08:00`, с `withoutOverlapping()` и `onOneServer()`.
- Это выглядит как единственный явный cron-schedule в коде.

Источник: `routes/console.php`.

### Queue / workers

- Queue default - `database`.
- Failed jobs хранятся в `failed_jobs`.
- Есть queue UI в `QueueController` и `resources/js/modules/Core/Pages/Queues/Index.vue` для просмотра очередей и ретраев.
- В dev-скрипте Composer используется `php artisan queue:listen --tries=1 --timeout=0`.
- В проекте есть `laravel/horizon`, но отдельной supervisor-конфигурации в изученных source-файлах не найдено.

Источник: `config/queue.php`, `app/Modules/Core/Http/Controllers/QueueController.php`, `resources/js/modules/Core/Pages/Queues/Index.vue`, `composer.json`.

### Дополнительные служебные контуры

- Есть сервисы для безопасной загрузки/раздачи файлов: `app/Services/FileStorage/*` и `app/Http/Controllers/FileDownloadController.php`.
- Есть отдельный monitoring UI для очередей и доставок уведомлений.

Источник: `app/Services/FileStorage/*`, `app/Http/Controllers/FileDownloadController.php`, `app/Modules/Core/Http/Controllers/QueueController.php`.

## 12. Документация проекта

Документации много, и она в целом совпадает с кодом.

Полезные документы:

- `docs/Architecture.md` и `docs/ModuleStructure.md` подтверждают модульный монолит.
- `docs/ProductScope.md` и `docs/FeatureMatrix.md` описывают реально найденные модули и фичи.
- `docs/UserRolesAndPermissions.md` совпадает с RBAC-реализацией в `spatie/laravel-permission`.
- `docs/entities/*.md` детализируют сущности и их поля.
- `docs/EXPORT_SERVICE.md`, `docs/PDF_EXPORT_SERVICE.md`, `docs/ShiftWorkflow.md`, `docs/Services.md` помогают понять специальные сервисы.

Источник: `docs/*`, сопоставлено с `app/*`, `resources/js/*`, `routes/*`.

Отдельный вывод: документация выглядит довольно актуальной и полезной, но это все равно дополнительный источник. В спорных местах приоритет у кода. Источник: `docs/*` и реальный код.

## 13. Завершенные части проекта

По факту кода завершенными выглядят следующие части:

- Auth и session-based web login.
- Core dashboard и персональные страницы профиля.
- RBAC на базе Spatie Permission.
- Пользователи, роли, сотрудники, филиалы, объекты, должности, очереди.
- Organizations с контактами, банковскими реквизитами, show-страницей, print и export.
- Equipment с полным набором журналов и карточкой оборудования.
- Documents с индексом, create/edit/show, версиями, файлами и связями.
- Shifts workflow: старт/финиш, lab/decoder отчеты, журналы и операции.
- File storage и signed file download.
- PDF export и Excel export.
- Notification scanning command и dashboard-уведомления.
- Набор feature/unit тестов по Core, Organizations, Equipment, Documents, Auth, Export, Telegram, Pdf, Services.

Источник: `routes/web.php`, `app/Modules/*`, `app/Services/*`, `tests/*`, `database/seeders/*`.

## 14. Незавершенные или спорные части проекта

Следующие места выглядят незавершенными или как минимум спорными:

- `app/Modules/Core/Http/Controllers/UserController.php` содержит TODO-методы `show`, `profile`, `updateProfile`, `changePassword`, но они не подключены к маршрутам.
- `app/Modules/Core/Services/PositionService.php` и `app/Modules/Core/DTO/PositionListItemData.php` содержат TODO по связи с пользователями.
- `app/Modules/Shifts/Providers/ShiftsServiceProvider.php` пустой; это не проблема само по себе, но выглядит как недоделанная модульная инициализация.
- В `resources/js/Pages/Index.vue` и `resources/js/Pages/PageTemplate.vue` лежат демонстрационные Vue-страницы.
- `app/Http/Controllers/IndexController.php` существует, но в `routes/web.php` не используется.
- Для Documents есть backend-операции `addFile`, `deleteFile`, `addRelation`, `deleteRelation`, `addVersion`, но не все они выглядят покрытыми отдельным UI.
- В `resources/js/modules/Core/Pages/Users/Index.vue` и похожих экранах заметна сильная опора на модалки и ajax-edit endpoints; это рабочий паттерн, но местами он делает код сложным.

Источник: `app/Modules/Core/Http/Controllers/UserController.php`, `app/Modules/Core/Services/PositionService.php`, `app/Modules/Shifts/Providers/ShiftsServiceProvider.php`, `resources/js/Pages/Index.vue`, `resources/js/Pages/PageTemplate.vue`, `app/Http/Controllers/IndexController.php`, `app/Modules/Documents/Http/Controllers/DocumentController.php`.

Если говорить строго, по части “незавершенности” без дополнительного контекста часть выводов остается предположением, потому что не все контроллеры и сервисы были изучены построчно. Для этих случаев источник указан, но степень уверенности средняя.

## 15. Расхождения между документацией и кодом

Существенных противоречий между документацией и кодом в изученных местах не найдено.

Что совпало:

- `docs/ProductScope.md` перечисляет 5 модулей, и код действительно показывает 5 модулей: Core, Organizations, Equipment, Documents, Shifts.
- `docs/Architecture.md` описывает модульный монолит с Inertia/Vue/Laravel, и код это подтверждает.
- `docs/UserRolesAndPermissions.md` соответствует фактическому RBAC и `Gate::before` для admin.

Что стоит отметить как не противоречие, а особенность:

- В документации описан модульный подход, но Organizations использует директорию `app/Modules/Organizations/Controllers`, а не `Http/Controllers`. Это не ошибка, а локальное отклонение от общего шаблона.
- `ShiftsServiceProvider` пустой, хотя модуль полностью подключен через общий `routes/web.php`. Документация на это не противоречит.

Источник: `docs/Architecture.md`, `docs/ProductScope.md`, `docs/UserRolesAndPermissions.md`, `app/Modules/Organizations/Controllers/*`, `app/Modules/Shifts/Providers/ShiftsServiceProvider.php`, `routes/web.php`.

## 16. Выводы для дальнейшего анализа

1. Проект уже имеет зрелый модульный каркас и заметный объем доменной реализации, особенно в Core, Equipment, Documents и Shifts.
2. Главная точка для следующего анализа - не структура, а качество отдельных доменных сценариев: формы, валидация, политика доступа, DTO и тестовое покрытие.
3. Наиболее полезно продолжать с инвентаризации конкретных сущностей и user flows, потому что общий каркас уже понятен.
4. Отдельно стоит проверить неиспользуемые демонстрационные страницы и TODO-места, чтобы понять, что является легаси, а что - просто техническим примером.

Источник: весь исследованный код и документация, особенно `app/Modules/*`, `resources/js/modules/*`, `tests/*`, `docs/*`.
