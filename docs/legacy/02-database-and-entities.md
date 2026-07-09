# База данных и доменные сущности старого проекта

## 1. Назначение отчета

Этот отчет фиксирует фактическую модель данных старой версии приложения:

- какие таблицы есть в БД;
- какие доменные сущности соответствуют этим таблицам;
- какие связи подтверждены миграциями и моделями;
- где сущности используются в сервисах, контроллерах, DTO, запросах и UI;
- какие есть расхождения, дубли и архитектурные риски.

Отчет составлен только по реальному коду и документации проекта. Если связь не задана явно в FK или модели, это помечено как логический вывод или предположение.

## 2. Изученные источники

### Основные источники истины

- `database/migrations/*`
- `app/Models/User.php`
- `app/Modules/*/Models/*.php`
- `app/Modules/*/Services/*.php`
- `app/Modules/*/Http/Controllers/*.php`
- `app/Modules/*/DTO/*.php`
- `app/Modules/*/Http/Requests/*.php`
- `routes/web.php`
- `routes/api.php`

### Дополнительные источники

- `docs/Entities.md`
- `docs/ER-Diagram.md`
- `docs/DomainModel.md`
- `docs/Notifications.md`
- `docs/Services.md`
- `docs/Workflows.md`
- `docs/UserRolesAndPermissions.md`
- `docs/entities/*.md`
- `docs/legacy-app-research/01-project-structure.md`

## 3. Общая модель данных

Система построена как модульный монолит с несколькими крупными доменами данных:

- System / Auth / RBAC;
- Core HR;
- Organizations;
- Objects;
- Equipment;
- Documents;
- Shifts;
- Notifications;
- Infrastructure tables Laravel.

Главные доменные центры данных:

- `employees` - центральная кадровая сущность;
- `objects` - производственные объекты;
- `equipment` - карточки оборудования и журналы жизненного цикла;
- `documents` - единый реестр документов;
- `shifts` - сменный workflow;
- `notification_deliveries` - журнал доставки уведомлений;
- `users` + Spatie RBAC - авторизация.

Сильные стороны модели:

- богатая предметная детализация;
- много явных FK и составных индексов;
- отдельные журналы под операции и статусы;
- есть документы, версии, теги и связи;
- есть централизованный журнал уведомлений.

Слабые стороны модели:

- есть смешение нескольких смыслов в одной таблице, особенно в `equipment`, `documents`, `employees`;
- часть таблиц не имеет локальных Eloquent-моделей и используется через `DB::table`;
- есть таблицы без явных внешних ключей;
- есть дублирующие поля-агрегаты и дублирующие представления одного объекта;
- есть смешение timezone-aware и timezone-unaware дат.

## 4. Список таблиц БД

### System / infrastructure

- `users`
- `password_reset_tokens`
- `sessions`
- `personal_access_tokens`
- `permissions`
- `roles`
- `model_has_permissions`
- `model_has_roles`
- `role_has_permissions`
- `activity_log`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

### Core HR

- `regions`
- `positions`
- `employees`
- `employee_qualifications`
- `employee_medical_examinations`
- `employee_briefings`
- `employee_ppe_items`
- `employee_trainings`
- `employee_documents`
- `branches`
- `branch_phones`
- `branch_emails`

### Objects / organizations

- `organizations`
- `organization_contacts`
- `organization_bank_accounts`
- `objects`
- `objects_customers`
- `object_equipment`

### Equipment

- `equipment_types`
- `equipment`
- `equipment_verifications`
- `equipment_calibrations`
- `equipment_maintenances`
- `equipment_assignments`
- `equipment_movements`
- `equipment_documents`
- `equipment_defects`

### Documents

- `document_types`
- `documents`
- `document_files`
- `document_versions`
- `document_relations`
- `document_tags`
- `document_tag_assignments`

### Shifts / production workflow

- `shifts`
- `shifts_labassistant`
- `film_types`
- `film_inventory_transactions`
- `developing_chemical_transactions`
- `developing_machine_maintenance_logs`
- `chemical_requests`
- `decoder_shift_film_groups`
- `decoder_shift_rejects`
- `decoder_shift_forgery_suspicions`
- `decoder_shift_cleanups`
- `decoder_shift_decryptions`
- `rs_types`

### Notifications

- `notification_deliveries`
- `telegram_messages`
- `sms_messages`
- `max_messages`

## 5. Подробное описание таблиц

### 5.1 System / инфраструктура

#### `users`

- Назначение: учетные записи для входа в систему.
- Модель: `App\Models\User`.
- Поля: `name`, `email`, `email_verified_at`, `password`, `remember_token`, `status`, `created_at`, `updated_at`, `deleted_at`.
- Типы: `email_verified_at` - `timestamp`; `status` - `enum(active, blocked)`; `deleted_at` - soft delete timestamp.
- PK: `id`.
- FK: нет.
- Индексы: unique `email`.
- Связи: `hasOne Employee`; RBAC через Spatie `HasRoles`.
- Использование: auth, admin UI, аудит `created_by`/`updated_by`, связь пользователя с сотрудником.
- Тип таблицы: таблица пользователей.
- Источники: миграции `0001_01_01_000000_create_users_table.php`, `2026_02_17_062056_add_soft_deletes_to_users_table.php`, `2026_02_20_000001_add_status_to_users_table.php`; модель `app/Models/User.php`.

#### `password_reset_tokens`

- Назначение: токены сброса пароля Laravel.
- Модель: нет.
- Поля: `email` PK, `token`, `created_at`.
- PK: `email`.
- FK: нет.
- Индексы: нет.
- Тип таблицы: служебная.
- Источник: `0001_01_01_000000_create_users_table.php`.

#### `sessions`

- Назначение: серверные сессии Laravel.
- Модель: нет.
- Поля: `id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`.
- PK: `id`.
- FK: нет.
- Индексы: `user_id`, `last_activity`.
- Тип таблицы: служебная.
- Источник: `0001_01_01_000000_create_users_table.php`.

#### `personal_access_tokens`

- Назначение: токены Laravel Sanctum.
- Модель: стандартная модель Sanctum, локальной доменной модели нет.
- Поля: `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, timestamps.
- PK: `id`.
- FK: морф-связь, без явного FK.
- Индексы: unique `token`, index `expires_at`.
- Тип таблицы: служебная.
- Источник: `2026_02_15_012407_create_personal_access_tokens_table.php`.

#### `permissions`

- Назначение: атомарные permissions Spatie RBAC.
- Модель: `App\Modules\Core\Models\Permission`.
- Поля: `name`, `guard_name`, `description`, timestamps.
- PK: `id`.
- FK: нет.
- Индексы: unique `(name, guard_name)`.
- Связи: `role_has_permissions`, `model_has_permissions`.
- Тип таблицы: справочник прав.
- Источник: `2026_02_16_000000_create_permission_tables.php`, `2026_02_17_000001_add_description_to_permissions_table.php`.

#### `roles`

- Назначение: роли Spatie RBAC.
- Модель: `App\Modules\Core\Models\Role`.
- Поля: `name`, `guard_name`, timestamps, optional `team_id` при включенном `permission.teams`.
- PK: `id`.
- FK: нет.
- Индексы: unique `(name, guard_name)` или `(team_id, name, guard_name)`.
- Связи: `role_has_permissions`, `model_has_roles`.
- Тип таблицы: справочник ролей.
- Источник: `2026_02_16_000000_create_permission_tables.php`.

#### `model_has_permissions`

- Назначение: полиморфная связь моделей с permissions.
- Модель: нет локальной.
- Поля: `permission_id`, `model_type`, `model_id`, optional `team_id`.
- PK: составной.
- FK: `permission_id -> permissions.id`.
- Индексы: `model_id + model_type`, optional `team_id`.
- Тип таблицы: таблица связей.
- Источник: `2026_02_16_000000_create_permission_tables.php`.

#### `model_has_roles`

- Назначение: полиморфная связь моделей с ролями.
- Модель: нет локальной.
- Поля: `role_id`, `model_type`, `model_id`, optional `team_id`.
- PK: составной.
- FK: `role_id -> roles.id`.
- Индексы: `model_id + model_type`, optional `team_id`.
- Тип таблицы: таблица связей.
- Источник: `2026_02_16_000000_create_permission_tables.php`.

#### `role_has_permissions`

- Назначение: связь роль -> permission.
- Модель: нет локальной.
- Поля: `permission_id`, `role_id`.
- PK: составной.
- FK: оба поля на Spatie таблицы.
- Индексы: PK.
- Тип таблицы: таблица связей.
- Источник: `2026_02_16_000000_create_permission_tables.php`.

#### `activity_log`

- Назначение: аудит изменений через Spatie Activitylog.
- Модель: пакетная, локальной модели нет.
- Поля: `log_name`, `description`, `subject_type`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `event`, timestamps.
- PK: `id`.
- FK: нет явных, только morph columns.
- Индексы: `log_name`.
- Тип таблицы: таблица логов / аудита.
- Источник: `2026_02_23_000003_create_activity_log_table.php`, `app/Services/ActivityLogService.php`.

#### `cache`

- Назначение: database cache store Laravel.
- Модель: нет.
- Поля: `key`, `value`, `expiration`.
- PK: `key`.
- FK: нет.
- Индексы: `expiration`.
- Тип таблицы: служебная.
- Источник: `0001_01_01_000001_create_cache_table.php`.

#### `cache_locks`

- Назначение: блокировки database cache store.
- Модель: нет.
- Поля: `key`, `owner`, `expiration`.
- PK: `key`.
- FK: нет.
- Индексы: `expiration`.
- Тип таблицы: служебная.
- Источник: `0001_01_01_000001_create_cache_table.php`.

#### `jobs`

- Назначение: queue jobs Laravel.
- Модель: нет.
- Поля: `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`.
- PK: `id`.
- FK: нет.
- Индексы: `queue`.
- Тип таблицы: служебная.
- Источник: `0001_01_01_000002_create_jobs_table.php`.

#### `job_batches`

- Назначение: batches очередей Laravel.
- Модель: нет.
- Поля: `id`, `name`, `total_jobs`, `pending_jobs`, `failed_jobs`, `failed_job_ids`, `options`, `cancelled_at`, `created_at`, `finished_at`.
- PK: `id`.
- FK: нет.
- Индексы: нет.
- Тип таблицы: служебная.
- Источник: `0001_01_01_000002_create_jobs_table.php`.

#### `failed_jobs`

- Назначение: проваленные задания очереди.
- Модель: нет.
- Поля: `uuid`, `connection`, `queue`, `payload`, `exception`, `failed_at`.
- PK: `id`.
- FK: нет.
- Индексы: unique `uuid`.
- Тип таблицы: таблица логов / очередей.
- Источник: `0001_01_01_000002_create_jobs_table.php`.

#### `regions`

- Назначение: справочник регионов.
- Модель: `App\Models\Region` (используется через `Organization::regionModel()` и seeder).
- Поля: `code`, `name`, `is_active`, `sort_order`, timestamps, `deleted_at`.
- PK: `id`.
- FK: нет.
- Индексы: unique `code`, unique `name`, composite `(is_active, sort_order)`.
- Тип таблицы: справочник.
- Источник: `2026_03_02_000001_create_regions_table.php`, `RegionSeeder.php`.

### 5.2 Core HR

#### `positions`

- Назначение: справочник должностей.
- Модель: `App\Modules\Core\Models\Position`.
- Поля: `title`, `code`, `role`, `is_active`, `sort_order`, timestamps, `deleted_at`.
- PK: `id`.
- FK: нет.
- Индексы: unique `title`, unique `code`, composite `(is_active, sort_order)`.
- Связи: в модели есть `users()` как `hasMany`, но в текущих миграциях у `users` нет `position_id`. Это расхождение модели и БД.
- Тип таблицы: справочник.
- Источник: `2026_02_23_000001_create_positions_table.php`, `app/Modules/Core/Models/Position.php`.

#### `employees`

- Назначение: кадровая карточка сотрудника НК.
- Модель: `App\Modules\Core\Models\Employee`.
- Поля: `user_id`, `email`, `telegram_user_id`, `max_user_id`, `notify_email`, `notify_sms`, `notify_telegram`, `notify_max`, `status`, `last_name`, `first_name`, `middle_name`, `birth_date`, `phone`, `work_phone`, `work_email`, `snils`, `inn`, `passport_series`, `passport_number`, `passport_issued_by`, `passport_issued_at`, `registration_address`, `actual_address`, `position_id`, `branch_id`, `object_id`, `object_name`, `personnel_number`, `hired_at`, `fired_at`, `contract_type`, `work_type`, `salary`, `height_cm`, `clothing_size`, `shoe_size`, `headgear_size`, `ring_size`, `respirator`, timestamps, `deleted_at`.
- Типы: `birth_date`, `hired_at`, `fired_at` - `date`; `salary` - decimal; размеры - integer; `notify_*` - boolean; sensitive fields - `binary` в БД и шифруются в модели; `passport_issued_at` хранится как binary, а в модели работает как дата.
- PK: `id`.
- FK: `user_id -> users.id` (set null), `position_id -> positions.id` (null on delete), `branch_id -> branches.id` (null on delete), `object_id -> objects.id` (null on delete).
- Индексы: unique `email`, `(status, created_at)`, `(last_name, first_name, middle_name)`, `branch_id`, `object_id`.
- Связи: hasOne `User`; hasMany `EmployeeQualification`, `EmployeeMedicalExamination`, `EmployeeBriefing`, `EmployeePpeItem`, `EmployeeTraining`, `EmployeeDocument`; belongsTo `Position`, `Branch`, `ProjectObject`.
- Тип таблицы: рабочая таблица / таблица пользователей предметной области.
- Источники: `2026_02_25_000001_create_employees_table.php`, `2026_03_04_000002_add_messenger_ids_to_employees_table.php`, `2026_03_04_000003_add_notification_flags_to_employees_table.php`, `2026_03_16_000001_add_object_id_to_employees_table.php`, `2026_03_19_120000_add_work_contacts_to_employees_table.php`, `app/Modules/Core/Models/Employee.php`, `app/Modules/Core/Services/EmployeeService.php`, `app/Modules/Core/Http/Controllers/EmployeeController.php`.

#### `employee_qualifications`

- Назначение: квалификации сотрудника.
- Модель: `App\Modules\Core\Models\EmployeeQualification`.
- Поля: `employee_id`, `type`, `level`, `certificate_number`, `issued_at`, `expires_at`, `control_object`, `attestation_center`, timestamps.
- PK: `id`.
- FK: `employee_id -> employees.id` cascade.
- Индексы: `(employee_id, expires_at)`, unique `(employee_id, certificate_number)`.
- Связи: belongsTo `Employee`.
- Тип таблицы: рабочая.
- Источники: `2026_02_25_000002_create_employee_qualifications_table.php`, `app/Modules/Core/Models/EmployeeQualification.php`.

#### `employee_medical_examinations`

- Назначение: медосмотры сотрудников.
- Модель: `App\Modules\Core\Models\EmployeeMedicalExamination`.
- Поля: `employee_id`, `type`, `examined_at`, `medical_organization`, `result`, `conclusion`, `next_exam_at`, `scan_path`, `notes`, timestamps.
- Типы: `examined_at`, `next_exam_at` - `date`; `conclusion` шифруется; `scan_path` был изменен миграцией на `text`.
- PK: `id`.
- FK: `employee_id -> employees.id` cascade.
- Индексы: `(employee_id, examined_at)`, `next_exam_at`.
- Тип таблицы: рабочая.
- Источники: `2026_02_25_000003_create_employee_medical_examinations_table.php`, `2026_03_04_000001_change_employee_file_path_columns_to_text.php`, `app/Modules/Core/Models/EmployeeMedicalExamination.php`.

#### `employee_briefings`

- Назначение: инструктажи.
- Модель: `App\Modules\Core\Models\EmployeeBriefing`.
- Поля: `employee_id`, `type`, `briefed_at`, `instructor_employee_id`, `status`, `repeated_at`, timestamps.
- Типы: `briefed_at`, `repeated_at` - `date`.
- FK: `employee_id -> employees.id` cascade; `instructor_employee_id -> employees.id` set null.
- Индексы: `(employee_id, briefed_at)`, `instructor_employee_id`, `(employee_id, repeated_at, status)`.
- Тип таблицы: рабочая.
- Источники: `2026_02_25_000004_create_employee_briefings_table.php`, `app/Modules/Core/Models/EmployeeBriefing.php`.

#### `employee_ppe_items`

- Назначение: выдача СИЗ.
- Модель: `App\Modules\Core\Models\EmployeePpeItem`.
- Поля: `employee_id`, `type`, `issued_at`, `duration_months`, `document`, `replacement_at`, timestamps.
- Типы: `issued_at`, `replacement_at` - `date`.
- FK: `employee_id -> employees.id` cascade.
- Индексы: `(employee_id, issued_at)`, `replacement_at`.
- Тип таблицы: рабочая.
- Источники: `2026_02_25_000005_create_employee_ppe_items_table.php`, `app/Modules/Core/Models/EmployeePpeItem.php`.

#### `employee_trainings`

- Назначение: обучения сотрудников.
- Модель: `App\Modules\Core\Models\EmployeeTraining`.
- Поля: `employee_id`, `type`, `training_center`, `started_at`, `finished_at`, `hours`, `document`, `status`, timestamps.
- Типы: `started_at`, `finished_at` - `date`; `hours` - integer.
- FK: `employee_id -> employees.id` cascade.
- Индексы: `(employee_id, started_at)`, `status`, `(started_at, status)`.
- Тип таблицы: рабочая.
- Источники: `2026_02_25_000006_create_employee_trainings_table.php`, `app/Modules/Core/Models/EmployeeTraining.php`.

#### `employee_documents`

- Назначение: документы сотрудника.
- Модель: `App\Modules\Core\Models\EmployeeDocument`.
- Поля: `employee_id`, `type`, `file_path`, `issued_at`, timestamps.
- Типы: `issued_at` - `date`; `file_path` позже преобразован в `text`.
- FK: `employee_id -> employees.id` cascade.
- Индексы: `(employee_id, type)`.
- Тип таблицы: рабочая / файловая.
- Источники: `2026_02_25_000007_create_employee_documents_table.php`, `2026_03_04_000001_change_employee_file_path_columns_to_text.php`, `app/Modules/Core/Models/EmployeeDocument.php`.

#### `branches`

- Назначение: филиалы компании.
- Модель: `App\Modules\Core\Models\Branch`.
- Поля: `name`, `address`, `responsible_employee_id`, `is_active`, timestamps, `deleted_at`.
- FK: `responsible_employee_id -> employees.id` unique.
- Индексы: `name`, unique `responsible_employee_id`, `is_active`.
- Связи: belongsTo `Employee`; hasMany `Employee`, `BranchPhone`, `BranchEmail`.
- Тип таблицы: справочник / организационная.
- Источники: `2026_03_03_000001_create_branches_tables.php`, `app/Modules/Core/Models/Branch.php`, `app/Modules/Core/Services/BranchService.php`.

#### `branch_phones`

- Назначение: телефоны филиалов.
- Модель: `App\Modules\Core\Models\BranchPhone`.
- Поля: `branch_id`, `phone`, `comment`, timestamps.
- FK: `branch_id -> branches.id` cascade.
- Индексы: `branch_id`.
- Тип таблицы: таблица связей / контактов.
- Источники: `2026_03_03_000001_create_branches_tables.php`, `app/Modules/Core/Models/BranchPhone.php`.

#### `branch_emails`

- Назначение: email-адреса филиалов.
- Модель: `App\Modules\Core\Models\BranchEmail`.
- Поля: `branch_id`, `email`, `comment`, timestamps.
- FK: `branch_id -> branches.id` cascade.
- Индексы: `branch_id`, `email`.
- Тип таблицы: таблица связей / контактов.
- Источники: `2026_03_03_000001_create_branches_tables.php`, `app/Modules/Core/Models/BranchEmail.php`.

### 5.3 Organizations / Objects

#### `organizations`

- Назначение: контрагенты / организации.
- Модель: `App\Modules\Organizations\Models\Organization`.
- Поля: `name`, `full_name`, `inn`, `kpp`, `ogrn`, `okpo`, `legal_address`, `postal_address`, `actual_address`, `region`, `ceo_full_name`, `vat_status`, `created_by`, `updated_by`, timestampsTz, softDeletesTz.
- Типы: `vat_status` - `enum(vat, no_vat)`; timestamps/softDeletes - timezone-aware.
- FK: `region -> regions.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `inn`, `kpp`, `name`, `region`, `deleted_at`, `created_by`, `updated_by`; на PostgreSQL есть check constraints и partial unique indexes.
- Связи: hasMany `OrganizationContact`, `OrganizationBankAccount`; belongsTo `Region`, `User`.
- Тип таблицы: справочник / рабочая.
- Источники: `2026_03_02_000002_create_organizations_tables.php`, `app/Modules/Organizations/Models/Organization.php`, `app/Modules/Organizations/Services/OrganizationService.php`.

#### `organization_contacts`

- Назначение: контактные лица организации.
- Модель: `App\Modules\Organizations\Models\OrganizationContact`.
- Поля: `organization_id`, `last_name`, `first_name`, `middle_name`, `job_title`, `phone`, `email`, `is_primary`, `comment`, `created_by`, `updated_by`, timestampsTz, softDeletesTz.
- FK: `organization_id -> organizations.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `organization_id`, `deleted_at`; на PostgreSQL есть partial indexes и уникальность primary-contact.
- Тип таблицы: рабочая / контактная.
- Источники: `2026_03_02_000002_create_organizations_tables.php`, `app/Modules/Organizations/Models/OrganizationContact.php`.

#### `organization_bank_accounts`

- Назначение: банковские реквизиты организации.
- Модель: `App\Modules\Organizations\Models\OrganizationBankAccount`.
- Поля: `organization_id`, `bank_name`, `bik`, `account_number`, `correspondent_account`, `is_default`, `created_by`, `updated_by`, timestampsTz, softDeletesTz.
- FK: `organization_id -> organizations.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `organization_id`, `bik`, `account_number`, `deleted_at`; на PostgreSQL есть unique default account.
- Тип таблицы: рабочая / реквизитная.
- Источники: `2026_03_02_000002_create_organizations_tables.php`, `app/Modules/Organizations/Models/OrganizationBankAccount.php`.

#### `objects`

- Назначение: производственные объекты / площадки.
- Модель: `App\Modules\Core\Models\ProjectObject`.
- Поля: `name`, `branch_id`, `address`, `date_start`, `date_end`, `operating_organization_id`, `operating_contact_name`, `operating_contact_phone`, `operating_contact_email`, `responsible_employee_id`, timestamps, `deleted_at`.
- Типы: `date_start`, `date_end` - `date`.
- FK: `branch_id -> branches.id`, `operating_organization_id -> organizations.id`, `responsible_employee_id -> employees.id`.
- Индексы: `branch_id`, `operating_organization_id`, `responsible_employee_id`, `date_start`, `date_end`, `name`; на PostgreSQL есть check `date_end >= date_start` и lower-name index.
- Связи: belongsTo `Branch`, `Organization`, `Employee`; belongsToMany `Organization` через `objects_customers`; belongsToMany `Equipment` через `object_equipment`.
- Тип таблицы: рабочая.
- Источники: `2026_03_04_000001_create_objects_tables.php`, `app/Modules/Core/Models/ProjectObject.php`, `app/Modules/Core/Services/ObjectService.php`, `app/Modules/Core/Http/Controllers/ObjectController.php`.

#### `objects_customers`

- Назначение: связи объект ↔ заказчик.
- Модель: отдельной Eloquent-модели нет, используется как pivot.
- Поля: `object_id`, `organization_id`, timestamps.
- PK: составной `(object_id, organization_id)`.
- FK: `object_id -> objects.id`, `organization_id -> organizations.id`.
- Индексы: `organization_id`.
- Тип таблицы: таблица связей.
- Источники: `2026_03_04_000001_create_objects_tables.php`, `app/Modules/Core/Models/ProjectObject.php`, `app/Modules/Core/Services/ObjectService.php`.

#### `object_equipment`

- Назначение: связи объект ↔ оборудование.
- Модель: `App\Modules\Core\Models\ObjectEquipment`.
- Поля: `object_id`, `equipment_id`, timestamps.
- PK: составной `(object_id, equipment_id)`.
- FK: `object_id -> objects.id`, `equipment_id -> equipment.id`.
- Индексы: `equipment_id`.
- Тип таблицы: таблица связей.
- Источники: `2026_03_20_000001_create_object_equipment_table.php`, `app/Modules/Core/Models/ObjectEquipment.php`, `app/Modules/Core/Models/ProjectObject.php`, `app/Modules/Equipment/Models/Equipment.php`.

### 5.4 Equipment

#### `equipment_types`

- Назначение: справочник типов оборудования.
- Модель: `App\Modules\Equipment\Models\EquipmentType`.
- Поля: `name`, `is_active`, `description`, `sort_order`, timestamps, `deleted_at`.
- FK: нет.
- Индексы: unique `name`, `is_active`, `sort_order`.
- Тип таблицы: справочник.
- Источники: `2026_03_07_000000_create_equipment_types_table.php`, `app/Modules/Equipment/Models/EquipmentType.php`, `app/Modules/Equipment/Services/EquipmentTypeService.php`.

#### `equipment`

- Назначение: карточка единицы оборудования.
- Модель: `App\Modules\Equipment\Models\Equipment`.
- Поля: `name`, `equipment_type_id`, `model`, `manufacturer`, `description`, `status`, `is_active`, `inventory_number`, `serial_number`, `passport_number`, `registration_number`, `barcode`, `qr_code`, `branch_id`, `commissioned_at`, `manufactured_at`, `purchased_at`, `service_life_until`, `last_used_at`, `usage_notes`, `requires_calibration`, `requires_verification`, `requires_attached_operator`, `can_be_assigned_to_project`, `verification_interval_days`, `last_verification_at`, `next_verification_at`, `verification_status`, `calibration_interval_days`, `last_calibration_at`, `next_calibration_at`, `calibration_status`, `metrology_notes`, `verification_document_file_id`, `calibration_document_file_id`, `responsible_employee_id`, `assigned_employee_id`, `issued_at`, `returned_at`, `responsibility_notes`, `condition`, `last_maintenance_at`, `next_maintenance_at`, `repair_status`, `retired_at`, `write_off_reason`, `maintenance_notes`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: mixed - `date`, `datetime`, `boolean`, `enum`, `integer`, `decimal` fields are not used here.
- FK: `equipment_type_id -> equipment_types.id`, `branch_id -> branches.id`, `responsible_employee_id -> employees.id`, `assigned_employee_id -> employees.id`, `created_by -> users.id`, `updated_by -> users.id`; `verification_document_file_id`, `calibration_document_file_id` не имеют явного FK.
- Индексы: `equipment_type_id`, `branch_id`, `status`, `condition`, `verification_status`, `calibration_status`, `responsible_employee_id`, `assigned_employee_id`, `next_verification_at`, `next_calibration_at`, `next_maintenance_at`, `verification_document_file_id`, `calibration_document_file_id`.
- Связи: belongsTo `EquipmentType`, `Branch`, `Employee` (two FK), `User`; belongsToMany `ProjectObject` через `object_equipment`.
- Тип таблицы: рабочая.
- Источники: `2026_03_07_000001_create_equipment_table.php`, `app/Modules/Equipment/Models/Equipment.php`, `app/Modules/Equipment/Services/EquipmentService.php`, `app/Modules/Equipment/Http/Controllers/EquipmentController.php`.

#### `equipment_verifications`

- Назначение: журнал поверок оборудования.
- Модель: `App\Modules\Equipment\Models\EquipmentVerification`.
- Поля: `equipment_id`, `verification_type`, `status`, `performed_at`, `valid_from`, `valid_until`, `next_verification_at`, `performed_by_organization_id`, `performed_by_employee_id`, `certificate_number`, `result`, `notes`, `document_file_path`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: `performed_at`, `valid_from`, `valid_until`, `next_verification_at` - `date`.
- FK: `equipment_id -> equipment.id`, `performed_by_organization_id -> organizations.id`, `performed_by_employee_id -> employees.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `equipment_id`, `status`, `performed_at`, `valid_until`, `next_verification_at`, composite index `equipment_id + valid_until` on PostgreSQL.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_07_000002_create_equipment_verifications_table.php`, `app/Modules/Equipment/Models/EquipmentVerification.php`.

#### `equipment_calibrations`

- Назначение: журнал калибровок оборудования.
- Модель: `App\Modules\Equipment\Models\EquipmentCalibration`.
- Поля: `equipment_id`, `calibration_type`, `status`, `performed_at`, `valid_from`, `valid_until`, `next_calibration_at`, `performed_by_organization_id`, `performed_by_employee_id`, `certificate_number`, `reference_values`, `result`, `notes`, `document_file_path`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: `performed_at`, `valid_from`, `valid_until`, `next_calibration_at` - `date`; `reference_values` - json/jsonb.
- FK: `equipment_id -> equipment.id`, `performed_by_organization_id -> organizations.id`, `performed_by_employee_id -> employees.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `equipment_id`, `status`, `performed_at`, `valid_until`, `next_calibration_at`, composite index `equipment_id + valid_until`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_07_000003_create_equipment_calibrations_table.php`, `app/Modules/Equipment/Models/EquipmentCalibration.php`.

#### `equipment_maintenances`

- Назначение: журнал обслуживания оборудования.
- Модель: `App\Modules\Equipment\Models\EquipmentMaintenance`.
- Поля: `equipment_id`, `maintenance_type`, `status`, `started_at`, `completed_at`, `next_maintenance_at`, `service_provider_type`, `service_provider_organization_id`, `service_provider_employee_id`, `cost_amount`, `downtime_days`, `description`, `result`, `notes`, `document_file_path`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: `started_at`, `completed_at`, `next_maintenance_at` - `date`; `cost_amount` - decimal; `downtime_days` - integer.
- FK: `equipment_id -> equipment.id`, `service_provider_organization_id -> organizations.id`, `service_provider_employee_id -> employees.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `equipment_id`, `maintenance_type`, `status`, `started_at`, `completed_at`, `next_maintenance_at`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_07_191955_create_equipment_maintenances_table.php`, `app/Modules/Equipment/Models/EquipmentMaintenance.php`.

#### `equipment_assignments`

- Назначение: выдача оборудования сотруднику или на объект.
- Модель: `App\Modules\Equipment\Models\EquipmentAssignment`.
- Поля: `equipment_id`, `employee_id`, `branch_id`, `assigned_by_employee_id`, `issued_at`, `planned_return_at`, `returned_at`, `issue_reason`, `issue_condition`, `return_condition`, `status`, `notes`, `acceptance_document_file_path`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: `issued_at`, `planned_return_at`, `returned_at` - `datetime`.
- FK: `equipment_id -> equipment.id`, `employee_id -> employees.id`, `branch_id -> branches.id`, `assigned_by_employee_id -> employees.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `equipment_id`, `employee_id`, `branch_id`, `status`, `issued_at`, `returned_at`; на PostgreSQL есть partial unique index на одну активную выдачу на оборудование.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_08_000001_create_equipment_assignments_table.php`, `app/Modules/Equipment/Models/EquipmentAssignment.php`.

#### `equipment_movements`

- Назначение: перемещения оборудования.
- Модель: `App\Modules\Equipment\Models\EquipmentMovement`.
- Поля: `equipment_id`, `movement_type`, `from_branch_id`, `to_branch_id`, `moved_by_employee_id`, `responsible_employee_id`, `moved_at`, `status`, `transport_info`, `notes`, `document_file_path`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: `moved_at` - `datetime`.
- FK: `equipment_id -> equipment.id`, `from_branch_id -> branches.id`, `to_branch_id -> branches.id`, `moved_by_employee_id -> employees.id`, `responsible_employee_id -> employees.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `equipment_id`, `movement_type`, `moved_at`, `status`, `from_branch_id`, `to_branch_id`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_08_120000_create_equipment_movements_table.php`, `app/Modules/Equipment/Models/EquipmentMovement.php`.

#### `equipment_documents`

- Назначение: документы оборудования.
- Модель: `App\Modules\Equipment\Models\EquipmentDocument`.
- Поля: `equipment_id`, `document_type`, `title`, `document_number`, `issued_at`, `valid_until`, `file_path`, `is_primary`, `notes`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: `issued_at`, `valid_until` - `date`; `is_primary` - boolean.
- FK: `equipment_id -> equipment.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `equipment_id`, `document_type`, `valid_until`.
- Уникальность: на PostgreSQL и SQLite есть частичный unique index для primary-by-type.
- Тип таблицы: файловая / рабочая.
- Источники: `2026_03_08_130000_create_equipment_documents_table.php`, `2026_03_20_120000_add_maintenance_regulation_to_equipment_document_type.php`, `app/Modules/Equipment/Models/EquipmentDocument.php`.

#### `equipment_defects`

- Назначение: дефекты оборудования.
- Модель: `App\Modules\Equipment\Models\EquipmentDefect`.
- Поля: `equipment_id`, `detected_at`, `reported_by_employee_id`, `defect_type`, `severity`, `status`, `title`, `description`, `impact_on_operation`, `maintenance_id`, `resolved_at`, `resolution_notes`, `document_file_path`, `created_by`, `updated_by`, timestamps, `deleted_at`.
- Типы: `detected_at`, `resolved_at` - `datetime`.
- FK: `equipment_id -> equipment.id`, `reported_by_employee_id -> employees.id`, `maintenance_id -> equipment_maintenances.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `equipment_id`, `status`, `severity`, `detected_at`, `maintenance_id`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_08_140000_create_equipment_defects_table.php`, `app/Modules/Equipment/Models/EquipmentDefect.php`.

### 5.5 Documents

#### `document_types`

- Назначение: справочник типов документов.
- Модель: `App\Modules\Documents\Models\DocumentType`.
- Поля: `code`, `name`, `description`, `is_contract`, `is_active`, `sort_order`, timestamps.
- PK: `id`.
- FK: нет.
- Индексы: unique `code`.
- Тип таблицы: справочник.
- Источники: `2026_03_09_000001_create_document_types_table.php`, `app/Modules/Documents/Models/DocumentType.php`, `app/Modules/Documents/Services/DocumentTypeService.php`.

#### `documents`

- Назначение: централизованный реестр документов.
- Модель: `App\Modules\Documents\Models\Document`.
- Поля: `document_type_id`, `title`, `description`, `status`, `is_confidential`, `is_original_received`, `is_signed`, `requires_renewal`, `registration_number`, `document_number`, `document_date`, `effective_date`, `expiry_date`, `signed_at`, `issuer_organization_id`, `owner_type`, `owner_id`, `branch_id`, `organization_id`, `responsible_employee_id`, `issued_at`, `received_at`, `valid_from`, `valid_to`, `renewal_deadline`, `termination_date`, `archive_date`, `superseded_by_document_id`, `parent_document_id`, `version_no`, `revision_comment`, `file_count`, `mime_summary`, `has_scan`, `has_signed_copy`, `created_by`, `updated_by`, `deleted_by`, timestamps, `deleted_at`.
- Типы: много `date`; `signed_at` - `datetime`; `is_*` - booleans; `version_no`/`file_count` - integers; owner_type - morph alias.
- FK: `document_type_id -> document_types.id`, `issuer_organization_id -> organizations.id`, `branch_id -> branches.id`, `organization_id -> organizations.id`, `responsible_employee_id -> employees.id`, `superseded_by_document_id -> documents.id`, `parent_document_id -> documents.id`, `created_by -> users.id`, `updated_by -> users.id`, `deleted_by -> users.id`.
- Индексы: множество индексов на FK, owner, status flags, dates, numbers; unique `(organization_id, registration_number)`.
- Связи: belongsTo `DocumentType`, `Organization`, `Branch`, `Employee`, `User`; morphTo `owner`; self-relations `parentDocument`/`supersededByDocument`; hasMany `DocumentFile`, `DocumentVersion`, `DocumentRelation`; belongsToMany `DocumentTag`.
- Тип таблицы: рабочая / реестр.
- Источники: `2026_03_09_000002_create_documents_table.php`, `app/Modules/Documents/Models/Document.php`, `app/Modules/Documents/Services/DocumentService.php`, `app/Modules/Documents/Http/Controllers/DocumentController.php`.

#### `document_files`

- Назначение: файлы документа.
- Модель: `App\Modules\Documents\Models\DocumentFile`.
- Поля: `document_id`, `file_storage_path`, `original_name`, `mime_type`, `file_size`, `file_role`, `version_no`, `uploaded_by`, timestamps.
- Типы: `file_size`, `version_no` - integers.
- FK: `document_id -> documents.id`, `uploaded_by -> users.id`.
- Индексы: `document_id`, `file_role`.
- Тип таблицы: файловая.
- Источники: `2026_03_09_000003_create_document_files_table.php`, `app/Modules/Documents/Models/DocumentFile.php`.

#### `document_versions`

- Назначение: история версий документа.
- Модель: `App\Modules\Documents\Models\DocumentVersion`.
- Поля: `document_id`, `version_no`, `title`, `status`, `revision_comment`, `created_by`, `created_at`.
- Типы: `created_at` - `timestamp`; модель отключает автоtimestamps.
- FK: `document_id -> documents.id`, `created_by -> users.id`.
- Индексы: `document_id`, `created_by`.
- Тип таблицы: таблица истории / лог изменений.
- Источники: `2026_03_09_000004_create_document_versions_table.php`, `app/Modules/Documents/Models/DocumentVersion.php`.

#### `document_relations`

- Назначение: связи между документами.
- Модель: `App\Modules\Documents\Models\DocumentRelation`.
- Поля: `document_id`, `related_document_id`, `relation_type`.
- FK: оба поля на `documents.id`.
- Индексы: `(document_id, related_document_id)`.
- Тип таблицы: таблица связей.
- Источники: `2026_03_09_000005_create_document_relations_table.php`, `app/Modules/Documents/Models/DocumentRelation.php`.

#### `document_tags`

- Назначение: теги документов.
- Модель: `App\Modules\Documents\Models\DocumentTag`.
- Поля: `name`, `color`, timestamps.
- FK: нет.
- Тип таблицы: справочник.
- Источники: `2026_03_09_000006_create_document_tags_table.php`, `app/Modules/Documents/Models/DocumentTag.php`.

#### `document_tag_assignments`

- Назначение: связь документ ↔ тег.
- Модель: `App\Modules\Documents\Models\DocumentTagAssignment`.
- Поля: `document_id`, `tag_id`.
- FK: оба поля на соответствующие таблицы.
- Уникальность: unique `(document_id, tag_id)`.
- Тип таблицы: таблица связей.
- Источники: `2026_03_09_000007_create_document_tag_assignments_table.php`, `app/Modules/Documents/Models/DocumentTagAssignment.php`.

### 5.6 Shifts / workflow

#### `shifts`

- Назначение: смены сотрудников на объектах.
- Модель: `App\Modules\Shifts\Models\Shift`.
- Поля: `employee_id`, `object_id`, `started_at`, `ended_at`, `shift_date`, `status`, `workflow`, `notes`, timestamps, `deleted_at`.
- Типы: `started_at`, `ended_at` - `timestampTz`; `shift_date` - `date`; `workflow` - string.
- FK: `employee_id -> employees.id`, `object_id -> objects.id`.
- Индексы: `(employee_id, shift_date)`, `(object_id, shift_date)`, `status`, `started_at`, `ended_at`, `workflow`.
- Связи: belongsTo `Employee`, `ProjectObject`; hasOne `ShiftLabassistant`; hasMany `DecoderShiftFilmGroup`, `DecoderShiftReject`, `DecoderShiftDecryption`, `FilmInventoryTransaction`, `DevelopingChemicalTransaction`, `DevelopingMachineMaintenanceLog`; hasOne `DecoderShiftForgerySuspicion`, `DecoderShiftCleanup`.
- Тип таблицы: рабочая / журнал смен.
- Источники: `2026_03_12_085109_create_shifts_table.php`, `2026_03_17_120000_add_workflow_to_shifts_table.php`, `app/Modules/Shifts/Models/Shift.php`, `app/Modules/Shifts/Services/ShiftStartService.php`, `app/Modules/Shifts/Services/ShiftFinisher.php`.

#### `shifts_labassistant`

- Назначение: итоговый отчет лаборанта по смене.
- Модель: `App\Modules\Shifts\Models\ShiftLabassistant`.
- Поля: `shift_id`, `machine_condition_status`, `machine_condition_comment`, `workplace_cleaned`, `workplace_cleaned_comment`, `comment`, `notes`, timestamps.
- FK: `shift_id -> shifts.id`.
- Тип таблицы: рабочая / итог смены.
- Источники: `2026_03_12_102845_create_shifts_laborant_table.php`, `app/Modules/Shifts/Models/ShiftLabassistant.php`.

#### `film_types`

- Назначение: справочник типов пленки.
- Модель: `App\Modules\Core\Models\FilmType`.
- Поля: `code`, `name`, `is_active`, timestamps, `deleted_at`.
- FK: нет.
- Индексы: unique `code`.
- Тип таблицы: справочник.
- Источники: `2026_03_13_082340_create_film_types_table.php`, `database/seeders/FilmTypeSeeder.php`, `app/Modules/Core/Models/FilmType.php`.

#### `film_inventory_transactions`

- Назначение: движение плёнки.
- Модель: `App\Modules\Shifts\Models\FilmInventoryTransaction`.
- Поля: `employee_id`, `object_id`, `shift_id`, `film_type_id`, `direction`, `operation_type`, `quantity_meters`, `issued_to_employee_id`, `received_from_employee_id`, `occurred_at`, `comment`, timestamps.
- Типы: `direction` and `operation_type` - enums from domain enums; `quantity_meters` - decimal; `occurred_at` - `timestampTz`.
- FK: `employee_id -> employees.id`, `object_id -> objects.id`, `shift_id -> shifts.id`, `film_type_id -> film_types.id`, `issued_to_employee_id -> employees.id`, `received_from_employee_id -> employees.id`.
- Индексы: `(employee_id, object_id, film_type_id)`, `(object_id, operation_type)`, `issued_to_employee_id`, `received_from_employee_id`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_13_082414_create_film_inventory_transactions_table.php`, `2026_03_26_120000_add_received_from_employee_id_to_film_inventory_transactions_table.php`, `app/Modules/Shifts/Models/FilmInventoryTransaction.php`.

#### `developing_chemical_transactions`

- Назначение: движение проявителя / фиксажa.
- Модель: `App\Modules\Shifts\Models\DevelopingChemicalTransaction`.
- Поля: `employee_id`, `object_id`, `shift_id`, `chemical_type`, `developing_machine_id`, `direction`, `operation_type`, `quantity_canisters`, `occurred_at`, `comment`, timestamps.
- Типы: `chemical_type` - enum developer/fixer; `direction` and `operation_type` - domain enums; `quantity_canisters` - integer; `occurred_at` - `timestampTz`.
- FK: `employee_id -> employees.id`, `object_id -> objects.id`, `shift_id -> shifts.id`, `developing_machine_id -> equipment.id`.
- Индексы: `(employee_id, object_id)`, `(object_id, chemical_type)` after alteration.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_13_082443_create_developing_chemical_transactions_table.php`, `2026_03_13_120000_add_chemical_type_to_developing_chemical_transactions_table.php`, `2026_03_21_000000_add_developing_machine_id_to_developing_chemical_transactions_table.php`, `app/Modules/Shifts/Models/DevelopingChemicalTransaction.php`.

#### `developing_machine_maintenance_logs`

- Назначение: чек-лист регламентных работ проявочной машины.
- Модель: `App\Modules\Shifts\Models\DevelopingMachineMaintenanceLog`.
- Поля: `employee_id`, `shift_id`, `object_id`, `maintenance_type`, `service_date`, набор `daily_*`, `weekly_*`, `monthly_*` boolean-пунктов, `comment`, timestamps.
- Типы: `service_date` - `date`; boolean-пункты nullable.
- FK: `employee_id -> employees.id`, `shift_id -> shifts.id`, `object_id -> objects.id`.
- Индексы: unique `(employee_id, maintenance_type, service_date)`, `(employee_id, service_date)`, `(maintenance_type, service_date)`, `(object_id, maintenance_type, service_date)`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_13_061834_create_developing_machine_maintenance_logs_table.php`, `2026_03_23_000001_add_object_id_to_developing_machine_maintenance_logs_table.php`, `app/Modules/Shifts/Models/DevelopingMachineMaintenanceLog.php`.

#### `chemical_requests`

- Назначение: заявки на химию.
- Модель: `App\Modules\Shifts\Models\ChemicalRequest`.
- Поля: `employee_id`, `object_id`, `shift_id`, `chemical_type`, `requested_quantity_canisters`, `status`, `requested_at`, `completed_at`, `received_at`, `received_quantity_canisters`, timestamps.
- Типы: `requested_at`, `completed_at`, `received_at` - `timestampTz`; `status` - enum `pending/completed/cancelled`.
- FK: `employee_id -> employees.id`, `object_id -> objects.id`, `shift_id -> shifts.id`.
- Индексы: `(status, requested_at)`, `(object_id, status)`, `(employee_id, status)`, `(chemical_type, status)`, `shift_id`.
- Тип таблицы: рабочая / workflow.
- Источники: `2026_03_25_100000_create_chemical_requests_table.php`, `app/Modules/Shifts/Models/ChemicalRequest.php`, `app/Modules/Shifts/Services/DevelopingChemicalService.php`.

#### `decoder_shift_film_groups`

- Назначение: журнал просмотренной пленки в decoder workflow.
- Модель: `App\Modules\Shifts\Models\DecoderShiftFilmGroup`.
- Поля: `shift_id`, `senior_ndt_employee_id`, `film_size_meters`, `exposures_per_joint`, `joints_count`, `films_count`, `film_length_meters`, timestamps.
- Типы: decimal + unsigned integer.
- FK: `shift_id -> shifts.id`, `senior_ndt_employee_id -> employees.id`.
- Индексы: `(shift_id, senior_ndt_employee_id)`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_16_100000_create_decoder_shift_reports_tables.php`, `app/Modules/Shifts/Models/DecoderShiftFilmGroup.php`.

#### `decoder_shift_rejects`

- Назначение: журнал брака в decoder workflow.
- Модель: `App\Modules\Shifts\Models\DecoderShiftReject`.
- Поля: `shift_id`, `senior_ndt_employee_id`, `reject_category`, `reject_reason`, `film_size_meters`, `rejected_films_count`, `rejected_joints_count`, `reject_length_meters`, `comment`, timestamps.
- Типы: decimal + unsigned integer.
- FK: `shift_id -> shifts.id`, `senior_ndt_employee_id -> employees.id`.
- Индексы: `(shift_id, senior_ndt_employee_id)`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_16_100000_create_decoder_shift_reports_tables.php`, `app/Modules/Shifts/Models/DecoderShiftReject.php`.

#### `decoder_shift_forgery_suspicions`

- Назначение: подозрения на подлог пленки.
- Модель: `App\Modules\Shifts\Models\DecoderShiftForgerySuspicion`.
- Поля: `shift_id`, `description`, timestamps.
- FK: `shift_id -> shifts.id`.
- Уникальность: unique `shift_id`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_16_100000_create_decoder_shift_reports_tables.php`, `app/Modules/Shifts/Models/DecoderShiftForgerySuspicion.php`.

#### `decoder_shift_cleanups`

- Назначение: уборка рабочего места в decoder workflow.
- Модель: `App\Modules\Shifts\Models\DecoderShiftCleanup`.
- Поля: `shift_id`, `is_completed`, `comment`, timestamps.
- FK: `shift_id -> shifts.id`.
- Уникальность: unique `shift_id`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_16_100000_create_decoder_shift_reports_tables.php`, `app/Modules/Shifts/Models/DecoderShiftCleanup.php`.

#### `decoder_shift_decryptions`

- Назначение: журнал расшифровки стыков.
- Модель: `App\Modules\Shifts\Models\DecoderShiftDecryption`.
- Поля: `shift_id`, `object_id`, `brought_by_employee_id`, `joint_number`, `joint_number_normalized`, `joint_number_digits`, `pipe_diameter_mm`, `wall_thickness_mm`, `is_rs`, `rs_type_id`, `is_acceptable`, `defect`, `created_by`, `updated_by`, timestamps.
- Типы: decimal, integer, boolean.
- FK: `shift_id -> shifts.id`, `object_id -> objects.id`, `brought_by_employee_id -> employees.id`, `rs_type_id -> rs_types.id`, `created_by -> users.id`, `updated_by -> users.id`.
- Индексы: `shift_id`, `object_id`, `brought_by_employee_id`, `joint_number`, `joint_number_normalized`, `joint_number_digits`, `is_acceptable`, `rs_type_id`.
- Тип таблицы: рабочая / журнал.
- Источники: `2026_03_31_100100_create_decoder_shift_decryptions_table.php`, `app/Modules/Shifts/Models/DecoderShiftDecryption.php`.

#### `rs_types`

- Назначение: справочник РС-типов.
- Модель: `App\Modules\Shifts\Models\RsType`.
- Поля: `code`, `name`, `sort_order`, `is_active`, timestamps.
- FK: нет.
- Индексы: unique `code`, composite `(is_active, sort_order)`.
- Тип таблицы: справочник.
- Источники: `2026_03_31_100000_create_rs_types_table.php`, `app/Modules/Shifts/Models/RsType.php`.

### 5.7 Notifications

#### `notification_deliveries`

- Назначение: единый журнал уведомлений.
- Модель: `App\Modules\Core\Models\NotificationDelivery`.
- Поля: `employee_id`, `channel`, `type`, `subject`, `body`, `body_sms`, `payload`, `priority`, `status`, `dedup_key`, `provider_message_id`, `error`, `code`, `response`, `created_by`, `created_at`, `processed_at`, `sent_at`.
- Типы: `payload`/`response` - jsonb; `priority`, `code` - integers; `status` - enum `pending/processing/sent/failed/skipped`; `channel` - enum `toastr/email/sms/telegram/max`.
- FK: `employee_id -> employees.id`, `created_by -> users.id`.
- Индексы: `(employee_id, channel, status)`, `(status, priority, created_at)`, `dedup_key` (unique in project docs / indexed in migration).
- Тип таблицы: таблица логов / workflow.
- Источники: `2026_03_04_000004_create_notification_deliveries_table.php`, `2026_03_23_120000_make_notification_deliveries_employee_id_nullable.php`, `2026_03_04_000008_add_unique_index_to_notification_deliveries_dedup_key.php`, `app/Services/NotificationService.php`, `app/Modules/Core/Models/NotificationDelivery.php`.

#### `telegram_messages`

- Назначение: channel-specific telegram payload.
- Модель: нет локальной Eloquent-модели, используется через `DB::table`.
- Поля: `delivery_id`, `chat_id`, `method`, `type`, `data`, `message_id`, `priority`, `status`, `error`, `code`, `response`, `created_at`, `processed_at`, `sent_at`.
- Типы: `data`/`response` - jsonb; `status` - enum `pending/processing/success/failed`.
- FK: `delivery_id -> notification_deliveries.id`.
- Индексы: `chat_id`, `method`, `type`, `priority`, `status`.
- Тип таблицы: таблица логов / канал.
- Источники: `2026_03_04_000005_create_telegram_messages_table.php`, `app/Services/Telegram/TelegramMessageService.php`.

#### `sms_messages`

- Назначение: channel-specific SMS payload.
- Модель: нет локальной Eloquent-модели, используется через `DB::table`.
- Поля: `delivery_id`, `phone`, `text`, `priority`, `status`, `error`, `code`, `response`, `created_at`, `processed_at`, `sent_at`, а также `provider_message_id` добавлен позднее.
- Типы: `response` - jsonb; `status` - enum `pending/processing/success/failed`.
- FK: `delivery_id -> notification_deliveries.id`.
- Индексы: `phone`, `priority`, `status`.
- Тип таблицы: таблица логов / канал.
- Источники: `2026_03_04_000007_create_sms_messages_table.php`, `2026_06_01_000001_add_provider_message_id_to_sms_messages_table.php`, `app/Services/NotificationService.php`, `app/Services/Notification/Smsc/SmscSmsService.php`.

#### `max_messages`

- Назначение: channel-specific MAX payload.
- Модель: нет локальной Eloquent-модели, используется через `DB::table`.
- Поля и структура почти зеркальны `telegram_messages`: `delivery_id`, `chat_id`, `method`, `type`, `data`, `message_id`, `priority`, `status`, `error`, `code`, `response`, `created_at`, `processed_at`, `sent_at`.
- Типы: `data`/`response` - jsonb; `status` - enum `pending/processing/success/failed`.
- FK: `delivery_id -> notification_deliveries.id`.
- Индексы: `chat_id`, `method`, `type`, `priority`, `status`.
- Тип таблицы: таблица логов / канал.
- Источники: `2026_03_04_000006_create_max_messages_table.php`, `app/Services/NotificationService.php`.

## 6. Доменные сущности

### Core domain

- `User` -> `users`
- `Role` -> `roles`
- `Permission` -> `permissions`
- `Employee` -> `employees`
- `Position` -> `positions`
- `Branch` -> `branches`
- `BranchPhone` -> `branch_phones`
- `BranchEmail` -> `branch_emails`
- `ProjectObject` -> `objects`
- `ObjectEquipment` -> `object_equipment`
- `NotificationDelivery` -> `notification_deliveries`
- `FilmType` -> `film_types`

### Organizations domain

- `Organization` -> `organizations`
- `OrganizationContact` -> `organization_contacts`
- `OrganizationBankAccount` -> `organization_bank_accounts`

### Equipment domain

- `EquipmentType` -> `equipment_types`
- `Equipment` -> `equipment`
- `EquipmentVerification` -> `equipment_verifications`
- `EquipmentCalibration` -> `equipment_calibrations`
- `EquipmentMaintenance` -> `equipment_maintenances`
- `EquipmentAssignment` -> `equipment_assignments`
- `EquipmentMovement` -> `equipment_movements`
- `EquipmentDocument` -> `equipment_documents`
- `EquipmentDefect` -> `equipment_defects`

### Documents domain

- `DocumentType` -> `document_types`
- `Document` -> `documents`
- `DocumentFile` -> `document_files`
- `DocumentVersion` -> `document_versions`
- `DocumentRelation` -> `document_relations`
- `DocumentTag` -> `document_tags`
- `DocumentTagAssignment` -> `document_tag_assignments`

### Shifts domain

- `Shift` -> `shifts`
- `ShiftLabassistant` -> `shifts_labassistant`
- `FilmInventoryTransaction` -> `film_inventory_transactions`
- `DevelopingChemicalTransaction` -> `developing_chemical_transactions`
- `DevelopingMachineMaintenanceLog` -> `developing_machine_maintenance_logs`
- `ChemicalRequest` -> `chemical_requests`
- `DecoderShiftFilmGroup` -> `decoder_shift_film_groups`
- `DecoderShiftReject` -> `decoder_shift_rejects`
- `DecoderShiftForgerySuspicion` -> `decoder_shift_forgery_suspicions`
- `DecoderShiftCleanup` -> `decoder_shift_cleanups`
- `DecoderShiftDecryption` -> `decoder_shift_decryptions`
- `RsType` -> `rs_types`

### Служебные / package tables без локальных моделей

- `password_reset_tokens`
- `sessions`
- `personal_access_tokens`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `activity_log`
- `model_has_permissions`
- `model_has_roles`
- `role_has_permissions`
- `objects_customers`
- `object_equipment`
- `telegram_messages`
- `sms_messages`
- `max_messages`

## 7. Связи между сущностями

### Явные FK

- `Employee -> User` через `employees.user_id`.
- `Employee -> Position` через `employees.position_id`.
- `Employee -> Branch` через `employees.branch_id`.
- `Employee -> ProjectObject` через `employees.object_id`.
- `Branch -> Employee` через `branches.responsible_employee_id`.
- `ProjectObject -> Branch` через `objects.branch_id`.
- `ProjectObject -> Organization` через `objects.operating_organization_id`.
- `ProjectObject -> Employee` через `objects.responsible_employee_id`.
- `Organization -> Region` через `organizations.region`.
- `Organization -> User` через `created_by` / `updated_by`.
- `Document -> DocumentType`.
- `Document -> Organization / Branch / Employee`.
- `Equipment -> EquipmentType / Branch / Employee / User`.
- `Shift -> Employee / ProjectObject`.
- `ChemicalRequest -> Employee / ProjectObject / Shift`.
- `FilmInventoryTransaction -> Employee / ProjectObject / Shift / FilmType`.
- `DevelopingChemicalTransaction -> Employee / ProjectObject / Shift / Equipment`.
- `NotificationDelivery -> Employee / User`.

### Таблицы связей

- `objects_customers`: `objects` ↔ `organizations`.
- `object_equipment`: `objects` ↔ `equipment`.
- `document_tag_assignments`: `documents` ↔ `document_tags`.
- `role_has_permissions`: `roles` ↔ `permissions`.
- `model_has_roles`: polymorphic model ↔ role.
- `model_has_permissions`: polymorphic model ↔ permission.

### Логические связи по коду

- `Document.owner_type/owner_id` - polymorphic owner, но в коде явно разрешены `organization`, `branch`, `employee`.
- `Shift.workflow` - строковый discriminator сценария завершения смены.
- `notification_deliveries.dedup_key` - ключ идемпотентности.
- `equipment.verification_document_file_id` и `calibration_document_file_id` похожи на ссылки на файловый реестр, но FK в миграции нет.

### Предположения

- `Position.role` выглядит как вспомогательная метка для RBAC, но не FK.
- `document.file_count`, `mime_summary`, `has_scan`, `has_signed_copy` выглядят как агрегаты/кэш представления.

## 8. Справочники

К справочникам относятся:

- `regions`
- `positions`
- `permissions`
- `roles`
- `document_types`
- `equipment_types`
- `film_types`
- `rs_types`
- `document_tags`

Часть из них - полноценные справочники предметной области, часть - технико-доменные справочники RBAC.

## 9. Рабочие таблицы

Рабочие таблицы и журналы:

- `employees`
- `employee_qualifications`
- `employee_medical_examinations`
- `employee_briefings`
- `employee_ppe_items`
- `employee_trainings`
- `employee_documents`
- `organizations`
- `organization_contacts`
- `organization_bank_accounts`
- `objects`
- `equipment`
- `equipment_verifications`
- `equipment_calibrations`
- `equipment_maintenances`
- `equipment_assignments`
- `equipment_movements`
- `equipment_documents`
- `equipment_defects`
- `documents`
- `document_files`
- `document_versions`
- `document_relations`
- `shifts`
- `shifts_labassistant`
- `film_inventory_transactions`
- `developing_chemical_transactions`
- `developing_machine_maintenance_logs`
- `chemical_requests`
- `decoder_shift_*`
- `notification_deliveries`
- `telegram_messages`
- `sms_messages`
- `max_messages`

## 10. Таблицы пользователей, ролей и прав

- `users`
- `roles`
- `permissions`
- `model_has_roles`
- `model_has_permissions`
- `role_has_permissions`

Особенности:

- доступ моделируется через Spatie RBAC;
- `admin` получает bypass через `Gate::before`;
- permissions создаются сидерами;
- `permissions` имеет `description`, что помогает строить читаемую матрицу прав;
- фронтенд скрывает элементы UI по permissions, но backend-проверки обязательны.

Источник: `database/seeders/PermissionsSeeder.php`, `database/seeders/DatabaseSeeder.php`, `app/Providers/AuthServiceProvider.php`, `docs/UserRolesAndPermissions.md`.

## 11. Таблицы файлов и вложений

- `employee_documents`
- `equipment_documents`
- `document_files`
- `documents` (агрегаты файлов и связи)
- `notification_deliveries` (payload и channel logs, но не сами файлы)

Наблюдение:

- во многих таблицах вместо file-entity используется строковый путь (`file_path`, `document_file_path`, `acceptance_document_file_path`, `file_storage_path`), а не отдельная централизованная файловая таблица;
- для документов и оборудования есть дублирующие агрегаты по файлам и состоянию файлов.

## 12. Таблицы логов, аудита и истории

- `activity_log`
- `document_versions`
- `notification_deliveries`
- `telegram_messages`
- `sms_messages`
- `max_messages`
- `jobs`
- `failed_jobs`
- `developing_machine_maintenance_logs`
- `film_inventory_transactions`
- `developing_chemical_transactions`
- `equipment_verifications`
- `equipment_calibrations`
- `equipment_maintenances`
- `equipment_movements`
- `equipment_defects`

## 13. Неиспользуемые или незавершенные таблицы

### Таблицы без локальных Eloquent-моделей

Точно без локальной доменной модели:

- `password_reset_tokens`
- `sessions`
- `personal_access_tokens`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `activity_log`
- `model_has_permissions`
- `model_has_roles`
- `role_has_permissions`
- `objects_customers`
- `object_equipment`
- `telegram_messages`
- `sms_messages`
- `max_messages`

### Таблицы с неполной или частично вынесенной логикой

- `telegram_messages`, `sms_messages`, `max_messages` пишутся напрямую через `DB::table`, а не через локальные модели.
- `equipment.verification_document_file_id` и `calibration_document_file_id` имеют полную бизнес-смысловую нагрузку, но в БД не оформлены FK.
- `Position::users()` объявлена в модели, но у `users` нет `position_id`, поэтому связь в текущей схеме не реализована.
- `ShiftLabassistant` имеет отдельную таблицу и используется как итоговый отчет, но имя таблицы отличается от модели-конвенции и это требует явного `$table`.

### Незавершенные признаки по workflow

- `shifts.status = cancelled` есть в схеме, но в runtime-логике переход не найден.
- `chemical_requests.status = cancelled` есть в enum, но явный переход в коде не найден.
- `notification_deliveries.channel = max` поддержан схемой, но рабочий транспортный клиент не найден.

## 14. Проблемы модели данных

### 14.1 Расхождения миграций и моделей

- `Position::users()` не поддержан FK в БД.
- В документации и DTO местами присутствуют поля, которых нет в миграции или наоборот:
  - `Position` DTO содержит `tenant_id`, но в таблице `positions` такого поля нет.
  - Это не критический runtime-bug для текущего отчета, но это факт несоответствия.
- `notification_deliveries.employee_id` было добавлено nullable отдельной миграцией - это нормальная эволюция схемы, но важно помнить о позднем изменении.

### 14.2 Отсутствие FK

- `equipment.verification_document_file_id`
- `equipment.calibration_document_file_id`
- `personal_access_tokens` - morph columns вместо явных FK, что нормально для Sanctum
- `activity_log` - morph columns вместо явных FK, что нормально для Spatie
- `telegram_messages.chat_id`, `message_id`, `method`, `data` - полностью логические поля

### 14.3 Отсутствие уникальных ограничений там, где они могли бы помочь

- `employee_documents` - нет уникальности по бизнес-ключу документа.
- `equipment_defects` - нет уникальности по сочетанию `equipment_id + detected_at + title`.
- `film_inventory_transactions` - нет явных уникальных ключей на бизнес-операции.
- `developing_chemical_transactions` - нет уникальности на дублирующиеся операции.

### 14.4 Дублирующие поля и смешение смыслов

- `employees`:
  - `email` и `work_email`;
  - `phone` и `work_phone`;
  - `object_id` и `object_name`;
  - `telegram_user_id` / `max_user_id` / `notify_*`.
- `equipment`:
  - `status`, `condition`, `verification_status`, `calibration_status`, `repair_status`, `is_active` - несколько статусов в одной таблице;
  - `issued_at`, `returned_at`, `assigned_employee_id`, `responsible_employee_id` - несколько смыслов закрепления;
  - `verification_document_file_id` / `calibration_document_file_id` - смешение файлового и доменного ключа.
- `documents`:
  - `owner_type`/`owner_id` вместе с `branch_id`, `organization_id`, `responsible_employee_id`;
  - `version_no` и отдельная `document_versions`;
  - `file_count`, `mime_summary`, `has_scan`, `has_signed_copy` как денормализованные агрегаты.
- `objects`:
  - `operating_organization_id` и `objects_customers` - разные типы связанности одной и той же организации с объектом.
- `notification_deliveries`:
  - `body`, `body_sms`, `payload`, `response`, `error`, `code`, `provider_message_id` - сильная смесь payload и диагностической информации.

### 14.5 Mixed timezone semantics

Факт из миграций:

- `shifts.started_at` / `shifts.ended_at` - `timestampTz`;
- `chemical_requests.requested_at/completed_at/received_at` - `timestampTz`;
- `film_inventory_transactions.occurred_at` - `timestampTz`;
- `developing_chemical_transactions.occurred_at` - `timestampTz`;
- `notification_deliveries.created_at/processed_at/sent_at` - `timestamp` без timezone;
- `documents.signed_at` - `dateTime` без явной timezone semantics.

Это означает, что в проекте смешаны timezone-aware и timezone-unaware поля. Для отчетов и UI это нужно учитывать отдельно.

### 14.6 Недостаточно данных для точного вывода

- Нельзя точно сказать, какие поля являются "мертвыми" без runtime-анализа использования во всех страницах, сервисах и экспортах.
- Особенно это касается:
  - `employees.object_name`
  - `employees.personnel_number`
  - `employees.ring_size`
  - `employees.respirator`
  - `documents.mime_summary`
  - `notification_deliveries.response`
  - `equipment.maintenance_notes`

## 15. Что можно использовать в новой системе

### Можно переносить почти без изменения концепции

- RBAC-модель: `users`, `roles`, `permissions`, pivot-таблицы.
- Кадровую модель сотрудника: `employees` + квалификации/медосмотры/инструктажи/СИЗ/обучение/документы.
- Модель филиалов.
- Модель объектов с заказчиками через pivot.
- Модель оборудования с жизненным циклом, журналами поверки, калибровки, ТО, выдач, перемещений и дефектов.
- Центральный реестр документов с версиями, файлами, тегами и связями.
- Сменный workflow, особенно если нужен лаборантский и decoder-сценарий.
- Централизованный журнал уведомлений с dedup и channel-specific детализацией.

### Можно использовать как паттерн, но не копировать без проверки

- Денормализованные агрегаты в `documents` и `equipment`.
- Статусные поля в `equipment` и `documents`.
- owner/morph-pattern в `documents`.
- Channel-specific notification tables.
- File path storage в строковых полях.

## 16. Что нельзя переносить без проверки

- Любые поля/связи без FK, если на них в новой версии требуется строгая целостность.
- Модель `equipment` в текущем виде без нормализации статусов и жизненного цикла.
- `documents` с одновременным использованием owner morph + нескольких прямых ссылок на организацию/филиал/сотрудника без пересмотра правил.
- `employees` с дублирующими текстовыми и ссылочными полями без единой политики происхождения данных.
- `Position::users()` без добавления реальной связи в БД.
- `notification_deliveries` без проверки, нужен ли `max`-канал вообще.
- Все timezone-unaware timestamp-поля без явной стратегии отображения и хранения.

