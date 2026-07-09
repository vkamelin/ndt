# Роли, права, статусы и жизненные циклы

## 1. Назначение отчета

Этот отчет фиксирует модель доступа и жизненные циклы сущностей в старой версии приложения.

Цели документа:

- восстановить роли пользователей и их фактические права;
- зафиксировать, где и как реализованы проверки доступа;
- перечислить статусы и состояния сущностей, найденные в коде;
- показать жизненные циклы и переходы между статусами;
- отделить подтвержденные факты от логических выводов и предположений;
- подготовить базу для проектирования новой версии системы без потери критичных правил.

Важно:

- данные ниже основаны на изучении таблиц, seed-данных, middleware, policies, контроллеров, сервисов, шаблонов и enum-классов;
- если вывод сделан не из явного имени роли/права, он помечен как `логический вывод`;
- если данных недостаточно, это явно указано как `Недостаточно данных для точного вывода`.

## 2. Изученные источники

### База и конфигурация

- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/config/auth.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/config/permission.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Providers/AuthServiceProvider.php`

### Пользователи и роли

- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/database/migrations/0001_01_01_000000_create_users_table.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/database/migrations/2026_02_20_000001_add_status_to_users_table.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/database/seeders/DatabaseSeeder.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/database/seeders/DecoderShiftPermissionsSeeder.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/database/seeders/LaborantShiftPermissionsSeeder.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/database/seeders/PositionSeeder.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Models/User.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Http/Middleware/CheckUserStatus.php`

### Политики и контроллеры

- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/UserPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/RolePolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/EmployeePolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/BranchPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/PositionPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/OrganizationPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/OrganizationContactPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/OrganizationBankAccountPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/DocumentPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Policies/ObjectPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Equipment/Policies/*`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Shifts/Policies/ShiftPolicy.php`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Http/Controllers/*`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Shifts/Http/Controllers/*`

### Сервисы и фронтенд

- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Core/Services/*`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/app/Modules/Shifts/Services/*`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/resources/js/components/Sidebar.vue`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/resources/js/components/Can.vue`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/resources/js/composables/useAuth.ts`
- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/resources/js/modules/Core/Pages/*`

### Миграции по статусам

- `/mnt/c/Users/vitaliy/Projects/NDT-Sentinel/database/migrations/*`

## 3. Пользователи

### 3.1. Таблица пользователей

Источник: миграции пользователей, модель `User`.

Факты:

- таблица `users` содержит `name`, `email`, `email_verified_at`, `password`, `remember_token`, `timestamps`;
- позже добавлено поле `status`;
- `status` хранится как enum со значениями `active` и `blocked`;
- `email_verified_at` nullable timestamp;
- модель `User` использует `HasRoles` и `SoftDeletes`;
- в модели есть проверки `isActive()` и `isBlocked()`;
- в модели есть scope `active()` и `blocked()`.

### 3.2. Seed-пользователи

Источник: `DatabaseSeeder`.

Подтвержденные seed-пользователи:

- `admin@example.com` с ролью `admin`;
- `lab@example.com` с ролью `lab`.

Дополнительные наблюдения:

- `user` роль создается в seed-данных, но в `DatabaseSeeder` никому не назначается;
- отдельные пользователи для ролей `decoder`, `hr`, `defectoscopist` в изученных seed-данных не найдены.

### 3.3. Жизненный цикл пользователя

Источник: `CheckUserStatus`, `UserService`, `UserPolicy`, `User` model.

Подтверждено:

- активный пользователь может работать в системе;
- заблокированный пользователь при попытке входа/использования приложения принудительно разлогинивается middleware;
- при редактировании себя пользователь не может менять собственные роли и статус;
- удалить самого себя нельзя;
- удалить последнего администратора нельзя.

Логический вывод:

- статус пользователя является глобальным переключателем доступа к системе, а не просто UI-меткой.

## 4. Роли

### 4.1. Подтвержденные роли

Источник: seed-данные ролей и permissions.

Подтвержденные роли:

- `admin`
- `lab`
- `decoder`
- `user`

### 4.2. Где роли определены

Источник: `DatabaseSeeder`, `DecoderShiftPermissionsSeeder`, `LaborantShiftPermissionsSeeder`, `PositionSeeder`, `EmployeeService`.

Факты:

- `admin`, `lab`, `decoder`, `user` создаются через seed-данные ролей;
- `admin` получает все permissions;
- `lab` и `decoder` получают ограниченные наборы permissions через специализированные seed-данные;
- в `PositionSeeder` встречаются значения `role = admin`, `hr`, `defectoscopist`, `lab`;
- `EmployeeService` синхронизирует роль пользователя по позиции сотрудника.

### 4.3. Спорные или неполные роли

Источник: `PositionSeeder`, seed-данные ролей, `EmployeeService`.

Зафиксировано:

- `hr` и `defectoscopist` существуют как значения в `PositionSeeder`;
- в изученных seed-данных ролей они не обнаружены;
- не найдено подтверждение, что эти роли реально создаются и используются как полноценные роли доступа.

Классификация:

- `hr` - `логический вывод`, что это задуманная роль, но реализация в текущем состоянии не подтверждена;
- `defectoscopist` - `логический вывод`, аналогично;
- `user` - подтвержденная роль, но по seed-данным выглядит как запасная/базовая роль без назначенных permissions.

### 4.4. Роль admin

Источник: `AuthServiceProvider`, `DatabaseSeeder`, `RolePolicy`, `Sidebar.vue`.

Факты:

- `Gate::before(...)` возвращает `true` для пользователя с ролью `admin`;
- это дает администратору обход всех policy-проверок;
- в UI есть админские разделы отчетов и журналов;
- seed-данные назначают `admin` полный набор permissions.

Логический вывод:

- `admin` является суперпользователем системы.

## 5. Права доступа

### 5.1. Общая модель permissions

Источник: `config/permission.php`, seed-данные permissions, policies.

Факты:

- используется Spatie Permission;
- teams отключены;
- wildcard permissions отключены;
- регистрации gates включены;
- проверки в коде в основном строятся через `can('permission')` и `hasRole(...)`;
- права имеют формат `entity.action`.

### 5.2. Основные группы permissions

Источник: seed-данные ролей и permissions.

Ниже зафиксированы группы прав, обнаруженные в системе:

- `user.*`
- `role.*`
- `permission.*`
- `position.*`
- `branch.*`, `branch_phone.*`, `branch_email.*`
- `employee.*`
- `employee_briefing.*`
- `employee_document.*`
- `employee_medical_examination.*`
- `employee_ppe_item.*`
- `employee_qualification.*`
- `employee_training.*`
- `organization.*`
- `organization_contact.*`
- `organization_bank_account.*`
- `project_object.*`
- `document.*`
- `document_type.*`
- `equipment.*`
- `equipment_assignment.*`
- `equipment_calibration.*`
- `equipment_document.*`
- `equipment_defect.*`
- `equipment_maintenance.*`
- `equipment_movement.*`
- `equipment_type.*`
- `equipment_verification.*`
- `shift.*`
- `chemical_request.*`
- `film_inventory_transaction.*`
- `developing_chemical_transaction.*`
- `decoder_shift.*`
- `decoder_shift_film_group.*`
- `decoder_shift_reject.*`
- `decoder_shift_forgery_suspicion.*`
- `decoder_shift_cleanup.*`
- `decoder_shift_decryption.*`
- `decoder_shift_report.*`
- `lab_shift_report.*`
- `notification_delivery.*`
- `queue.*`
- `activity_log.*`
- `settings.*`
- `region.*`
- `dashboard.view`

### 5.3. Подтвержденные permissions по ролям

Источник: seed-данные ролей и permissions.

#### admin

- получает все permissions;
- это не перечисление отдельных прав, а полный доступ.

#### lab

Подтвержденный набор:

- `dashboard.view`
- `employee_medical_examination.view_any`
- `employee_briefing.view_any`
- `employee_ppe_item.view_any`
- `shift.view_own`
- `shift.start`
- `shift.finish`
- `shift.record_film_receipt`
- `shift.record_chemical_receipt`
- `shift.replace_chemical`
- `shift.issue_film`
- `chemical_request.view_any`
- `chemical_request.view`
- `chemical_request.update`
- `lab_shift_report.view_any`
- `lab_shift_report.view`

#### decoder

Подтвержденный набор:

- `dashboard.view`
- `shift.view_own`
- `shift.start`
- `shift.finish`
- `decoder_shift.view`
- `decoder_shift_film_group.create`
- `decoder_shift_film_group.view_any`
- `decoder_shift_reject.create`
- `decoder_shift_reject.view_any`
- `decoder_shift_forgery_suspicion.view_any`
- `decoder_shift_forgery_suspicion.update`
- `decoder_shift_cleanup.update`
- `decoder_shift_decryption.view_any`
- `decoder_shift_decryption.view`
- `decoder_shift_decryption.create`
- `decoder_shift_decryption.update`
- `decoder_shift_decryption.delete`
- `decoder_shift_summary.view`
- `decoder_shift_report.view_any`
- `decoder_shift_report.view`

#### user

- роль существует;
- permissions ей в изученных seed-данных не назначены;
- фактический доступ для этой роли по current seed-данным определить нельзя.

### 5.4. Права, которые выглядят не полностью внедренными

Источник: seed-данные permissions, поиск по политикам/контроллерам/шаблонам.

По ряду permissions есть явное присутствие в seed-данных, но не найдено стабильное использование в коде:

- `settings.*`
- `permission.*`
- `project_object.archive`
- `region.change_status`
- часть `employee_document.*` и `employee_training.*`
- часть `equipment_type.*` и `equipment_defect.*`
- `shift.view_own`

Классификация:

- это не означает, что права ошибочны;
- это означает, что в изученном наборе файлов не найден полный жизненный цикл их применения.

## 6. Проверки доступа в коде

### 6.1. Централизованные проверки

Источник: policies, `AuthServiceProvider`, middleware.

Подтверждено:

- политики зарегистрированы в `AuthServiceProvider`;
- для `admin` есть глобальный bypass через `Gate::before`;
- заблокированный пользователь выкидывается middleware;
- некоторые страницы и действия защищены route middleware `can:...`;
- в UI также есть дополнительные проверки, но они не являются источником истины.

### 6.2. Где проверяется доступ

Источник: контроллеры, policies, middleware, шаблоны.

Проверки реализованы в нескольких слоях:

- `middleware` - блокировка пользователя по статусу;
- `policy` - основная модель авторизации;
- `controller` - `authorize`, `authorizeResource`, `abort_if`, route middleware;
- `service` - дополнительные доменные ограничения, например на сохранение данных, смену статусов и связи между сущностями;
- `frontend` - скрытие кнопок и разделов через `can()` и `<Can>`.

### 6.3. Примеры проверок

Источник: политики и контроллеры.

- `UserPolicy` - доступ к пользователям;
- `RolePolicy` - доступ к ролям;
- `EmployeePolicy` - доступ к сотрудникам;
- `BranchPolicy` - доступ к филиалам;
- `PositionPolicy` - доступ к должностям и переключению активности;
- `OrganizationPolicy`, `OrganizationContactPolicy`, `OrganizationBankAccountPolicy` - доступ к организациям и связанным сущностям;
- `DocumentPolicy` - доступ к документам;
- `ObjectPolicy` - доступ к объектам и операциям вроде archive/link/unlink/print/generate;
- `ShiftPolicy` - доступ к сменам;
- `QueueController` - отдельные route middleware для очереди и delivery;
- `DashboardController` - отдельная проверка на `dashboard.view`.

### 6.4. Что видно как нецентрализованность

Источник: сервисы, политики, шаблоны.

Наблюдение:

- часть проверок живет в policies;
- часть правил доступа дублируется в сервисах;
- часть UI-кнопок выключается отдельно на фронтенде;
- часть логики доступности страниц завязана на роли, а не только на permissions.

Вывод:

- модель прав не полностью централизована;
- система использует смешанный подход: `permissions + policies + role checks + service guards + UI guards`.

## 7. Матрица ролей и действий

### 7.1. Сводка по ролям

| Роль | Источник | Что подтверждено |
| --- | --- | --- |
| `admin` | seed, `Gate::before` | полный доступ ко всем permissions, bypass policy |
| `lab` | seed | доступ к лабораторным операциям, сменам и части отчетов |
| `decoder` | seed | доступ к декодированию, связанным сущностям и части смен |
| `user` | seed | роль существует, но права в seed-данных не назначены |
| `hr` | `PositionSeeder` | `логический вывод`, что роль задумана, но не подтверждена seed-данными ролей |
| `defectoscopist` | `PositionSeeder` | `логический вывод`, что роль задумана, но не подтверждена seed-данными ролей |

### 7.2. Действия по ролям

#### admin

Источник: `Gate::before`, seed, policies.

- просматривает все разделы;
- создает, изменяет, удаляет сущности, где это разрешено политиками;
- видит админские разделы меню;
- может обходить policy-ограничения.

#### lab

Источник: seed, `LaborantShiftStartService`, `LaborantShiftCompletionService`, `LabShiftReportController`, sidebar.

- начинает и завершает свою смену;
- фиксирует выдачу пленки и химии;
- заменяет химию;
- просматривает лабораторные отчеты;
- просматривает собственные/доступные медицинские осмотры, инструктажи, СИЗ;
- работает с `chemical_request` в рамках выданных прав.

#### decoder

Источник: seed, `DecoderShiftStartService`, `DecoderShiftCompletionService`, `DecoderShiftReportController`, sidebar.

- начинает и завершает свою смену;
- создает и редактирует элементы декодирования;
- просматривает декодерные отчеты;
- работает с группами пленок, браком, подозрениями на подделку, очисткой и дешифровкой.

#### user

Источник: seed.

- подтвержденных permissions в seed-данных нет;
- фактический набор действий определить нельзя.

### 7.3. Какие сущности каждая роль может создавать, просматривать, изменять и удалять

Источник: policies, seed, контроллеры.

- `admin` - все сущности, для которых есть permissions и policy;
- `lab` - смены, химические запросы, лабораторные отчеты, часть производственных операций;
- `decoder` - смены, связанные сущности декодирования;
- `user` - недостаточно данных для точного вывода.

## 8. Разграничение доступа по данным

### 8.1. Подтвержденные ограничения по контексту данных

Источник: сервисы и контроллеры.

Есть не только глобальные permissions, но и дополнительные доменные ограничения:

- отчет по смене может зависеть от роли и принадлежности сотрудника;
- в лабораторных действиях учитывается текущий филиал;
- при работе с сотрудниками учитывается связка `employee -> branch -> object -> position`;
- удаление некоторых сущностей запрещено при наличии зависимых записей;
- часть операций возможна только для собственной смены.

### 8.2. Где есть привязка к организации/объекту/филиалу/исполнителю

Источник: сервисы, контроллеры, policies.

Подтверждено:

- по филиалу - есть проверки в сервисах смен и выдачи материалов;
- по сотруднику - есть правила на собственную смену и own-report сценарии;
- по объекту - используется в связанности сотрудников и оборудования;
- по организации - доступ к организациям, контактам, банкам контролируется отдельными policy;
- по исполнителю - в отдельных сервисах и отчетах.

### 8.3. Чего не видно

Источник: изученные файлы.

Недостаточно данных для точного вывода:

- есть ли универсальная row-level ACL по `organization_id` или `object_id` для всех сущностей;
- есть ли жесткая привязка прав к конкретным филиалам через pivot-таблицы;
- есть ли отдельные ограничители по участку или зоне контроля.

Вывод:

- разграничение доступа частично контекстное, но не выглядит как единая централизованная data-scoped ACL-модель.

## 9. Статусы сущностей

### 9.1. Пользователи

Источник: миграция users, `User` model, middleware.

Статусы:

- `active`
- `blocked`

Где используются:

- в таблице `users`;
- в модели `User`;
- в middleware блокировки;
- в UI форм и списков пользователей;
- в сервисе управления пользователями.

Кто меняет:

- администратор или пользователь с правом `user.change_status`;
- self-edit не позволяет менять собственный статус.

### 9.2. Сотрудники

Источник: `EmployeeService`, `EmployeePolicy`, frontend pages, migrations.

Статусы:

- `active`
- `vacation`
- `sick_leave`
- `maternity_leave`
- `business_trip`
- `terminated`
- `suspended_hr`

Где используются:

- в таблице сотрудников;
- в фильтрах списка и карточках;
- в сервисах и отчетах;
- в логике отображения и счетчиков dashboard.

Кто меняет:

- пользователи с соответствующим доступом к сотрудникам;
- точный механизм переходов внутри каждого статуса по найденным файлам не полностью централизован.

### 9.3. Должности

Источник: `PositionSeeder`, `PositionPolicy`, `PositionService`.

Состояние:

- `is_active` boolean.

Где используется:

- в списках и фильтрах должностей;
- при назначении должности сотруднику;
- при синхронизации ролей через `EmployeeService`.

### 9.4. Филиалы

Источник: `BranchService`, `BranchPolicy`, migrations.

Состояние:

- `is_active` boolean.

Где используется:

- при выборе филиалов;
- в контроле удаления;
- в производственных сценариях сотрудников и оборудования.

### 9.5. Организации

Источник: `OrganizationService`, `OrganizationPolicy`, migrations.

Состояние:

- `vat_status`:
  - `vat`
  - `no_vat`

Дополнительно:

- soft delete / restore.

### 9.6. Документы

Источник: `DocumentService`, `DocumentPolicy`, migrations.

Статусы:

- `draft`
- `active`
- `expired`
- `terminated`
- `archived`
- `superseded`
- `cancelled`

Где используются:

- в таблицах и фильтрах документов;
- в версиях документов;
- в отчетах и карточках;
- в действиях обновления и архивирования.

### 9.7. Типы документов

Источник: `DocumentTypeService`, migrations.

Состояние:

- `is_active` boolean.

### 9.8. Оборудование

Источник: `EquipmentService` и связанные сервисы, migrations.

Статусы:

- `status`:
  - `active`
  - `in_storage`
  - `under_maintenance`
  - `under_calibration`
  - `retired`
  - `written_off`
- `condition`:
  - `new`
  - `good`
  - `needs_attention`
  - `faulty`
  - `out_of_service`
- `verification_status`:
  - `valid`
  - `expiring_soon`
  - `expired`
  - `not_required`
- `calibration_status`:
  - `valid`
  - `expiring_soon`
  - `expired`
  - `not_required`
- `repair_status`:
  - `none`
  - `planned`
  - `in_progress`
  - `completed`

### 9.9. Проверки, калибровки, ремонты, перемещения и дефекты оборудования

Источник: `EquipmentVerificationService`, `EquipmentCalibrationService`, `EquipmentMaintenanceService`, `EquipmentMovementService`, `EquipmentDefectService`, migrations.

Подсущности и статусы:

- `equipment_verifications.status`:
  - `scheduled`
  - `passed`
  - `failed`
  - `cancelled`
  - `expired`
- `equipment_calibrations.status`:
  - `scheduled`
  - `passed`
  - `failed`
  - `cancelled`
  - `expired`
- `equipment_maintenances.status`:
  - `planned`
  - `in_progress`
  - `completed`
  - `cancelled`
- `equipment_assignments.status`:
  - `active`
  - `returned`
  - `lost`
  - `damaged`
  - `cancelled`
- `equipment_movements.status`:
  - `planned`
  - `completed`
  - `cancelled`
- `equipment_defects.status`:
  - `open`
  - `in_review`
  - `in_repair`
  - `resolved`
  - `closed`
  - `cancelled`

### 9.10. Смены

Источник: `Shift` model, shift services, policies, controllers.

Статусы:

- `open`
- `closed`
- `cancelled`

### 9.11. Химические запросы

Источник: `ChemicalRequestService`, controller, migrations.

Статусы:

- `pending`
- `completed`
- `cancelled`

### 9.12. Инструктажи, обучения, медицинские осмотры

Источник: `EmployeeBriefing`, `EmployeeTraining`, `EmployeeMedicalExamination` related code.

Статусы и результаты:

- `employee_briefings.status`:
  - `completed`
  - `failed`
  - `pending`
- `employee_trainings.status`:
  - `pending`
  - `in_progress`
  - `completed`
  - `not_completed`
- `employee_medical_examinations.result`:
  - `fit`
  - `fit_with_limitations`
  - `unfit`

### 9.13. Очередь и уведомления

Источник: queue controllers, notification delivery code.

Статусы:

- `notification_deliveries.status`:
  - `pending`
  - `processing`
  - `sent`
  - `failed`
  - `skipped`
- сообщения Telegram / SMS / batch queue:
  - `pending`
  - `processing`
  - `success`
  - `failed`

## 10. Жизненные циклы сущностей

### 10.1. Пользователь

Источник: middleware, service, policy, seed.

Жизненный цикл:

1. создается;
2. получает роль;
3. активен или заблокирован;
4. при блокировке не может использовать систему;
5. может быть удален, но не может удалить себя и последнего администратора.

### 10.2. Смена

Источник: `LaborantShiftStartService`, `DecoderShiftStartService`, completion services, `ShiftStateService`.

Жизненный цикл:

1. `open` при старте;
2. выполняются рабочие действия;
3. `closed` при завершении;
4. `cancelled` как отдельный сценарий;
5. `ended_at` фиксирует конец смены, а открытая смена определяется через `ended_at IS NULL`.

### 10.3. Документ

Источник: `DocumentService`, `DocumentPolicy`.

Жизненный цикл:

1. `draft`;
2. `active`;
3. далее возможны `expired`, `terminated`, `archived`, `superseded`, `cancelled`;
4. используются версии документов;
5. смена статуса сопровождается логированием.

### 10.4. Оборудование

Источник: `EquipmentService` и related services.

Жизненный цикл:

1. оборудование создается;
2. может находиться в `in_storage` или `active`;
3. уходит на обслуживание, калибровку, проверку или ремонт;
4. может получить дефект;
5. может быть выведено из эксплуатации или списано;
6. состояние агрегируется из связанных записей движения, ремонта, дефектов, поверки и калибровки.

### 10.5. Химический запрос

Источник: `ChemicalRequestController`, service.

Жизненный цикл:

1. `pending`;
2. `completed`;
3. `cancelled`.

### 10.6. Инструктаж / обучение / медосмотр

Источник: соответствующие сервисы и страницы.

Жизненные циклы:

- инструктаж:
  - `pending` -> `completed` или `failed`;
- обучение:
  - `pending` -> `in_progress` -> `completed` или `not_completed`;
- медосмотр:
  - результат фиксируется напрямую без полноценной state-machine.

## 11. Переходы между статусами

### 11.1. Пользователь

Источник: `UserService`, `CheckUserStatus`.

Переходы:

- `active` -> `blocked`;
- `blocked` -> `active`.

Условия:

- доступно только через права управления пользователями;
- self-edit не позволяет менять свой статус;
- blocked пользователь выводится из сессии.

### 11.2. Смена

Источник: shift services.

Переходы:

- `open` -> `closed`;
- `open` -> `cancelled` `логический вывод`, так как статус предусмотрен, но конкретный сценарий отмены не был явно изучен в полноте;
- `closed` и `cancelled` выглядят конечными.

Условия:

- наличие нужной роли;
- наличие открытой смены;
- для декодера требуется заполненная логика по пленкам и очистке;
- для лаборанта требуется выполнение предусмотренных операций.

### 11.3. Документ

Источник: `DocumentService`.

Переходы:

- `draft` -> `active`;
- `active` -> `expired`;
- `active` -> `terminated`;
- `active` -> `archived`;
- `active` -> `superseded`;
- `active` -> `cancelled`;
- возможны и обратные движения через редактирование версий, но полная матрица переходов не восстановлена.

Условия:

- зависят от бизнес-операции и текущих дат;
- часть полей дат используется для вычисления жизненного цикла;
- полная централизованная state machine не обнаружена.

### 11.4. Оборудование

Источник: equipment services.

Подтвержденные переходы:

- проверка или калибровка может менять агрегированный статус и сроки следующего события;
- ремонт переводит оборудование в `under_maintenance`;
- перемещение на проверку/калибровку переводит в `under_calibration`;
- списание переводит в `written_off`;
- открытые дефекты с `operation_prohibited` могут блокировать эксплуатацию.

Условия:

- наличие завершенного связанного события;
- соблюдение доменных проверок;
- отсутствие конфликтующих активных записей;
- наличие нужного статуса связанной сущности.

### 11.5. Химический запрос

Источник: `ChemicalRequestController`.

Переходы:

- `pending` -> `completed`;
- `pending` -> `cancelled`.

Условия:

- completion route фиксирует получение и количество;
- cancel route в изученных фрагментах не был подтвержден.

## 12. Незавершенные или спорные роли и права

### 12.1. Роли

Источник: seed-данные и `PositionSeeder`.

Спорные элементы:

- `hr` - встречается в позициях, но не подтверждено как role seed;
- `defectoscopist` - аналогично;
- `user` - роль есть, но без прав в изученных seed-данных, поэтому ее практический смысл не подтвержден.

### 12.2. Права

Источник: seed-данные permissions и поиск по коду.

Спорные или не до конца внедренные permissions:

- `settings.*`
- `permission.*`
- `project_object.archive`
- `region.change_status`
- часть `employee_document.*`
- часть `employee_training.*`
- часть `equipment_type.*`
- `shift.view_own`

Комментарий:

- наличие permission в seed-данных еще не означает, что по нему есть полный пользовательский сценарий;
- для части прав найден только policy или только seed, но не найден полный путь от UI до сервисов.

## 13. Незавершенные или спорные статусы

### 13.1. Статусы, которые выглядят несобранными

Источник: migrations, services, controllers.

Сомнительные места:

- `project_object.archive` permission есть, но полноценный статус/жизненный цикл объекта в изученных файлах не восстановлен;
- у `document` много статусов, но матрица переходов не полностью формализована в одном месте;
- у оборудования большое число статусов, часть из них агрегируется из разных подсущностей, что делает жизненный цикл распределенным;
- у `employee_medical_examinations` есть результат, но нет явного status lifecycle;
- у `employee_ppe_items` жизненный цикл вычисляется по датам, а не по status полю.

### 13.2. Технические признаки незавершенности

Источник: изученные сервисы и контроллеры.

- часть прав присутствует без явно найденных UI-маршрутов;
- часть фильтров по статусам есть, но не вся логика статуса собрана в одном enum-классе;
- часть переходов выполнена через сервисы, а не через единый state machine;
- часть названий ролей/должностей не совпадает между seed-данными ролей и seed-данными позиций.

## 14. Что можно использовать в новой системе

Источник: весь изученный набор.

Можно переносить как устойчивые паттерны:

- `admin` как суперроль с глобальным bypass через `Gate::before`;
- разделение на `lab` и `decoder` как реальные рабочие роли;
- модель `permissions` в формате `entity.action`;
- middleware для немедленной блокировки `blocked` пользователей;
- `soft delete` для ряда справочников;
- domain guards в сервисах для смен, оборудования и документов;
- раздельные policies по сущностям;
- UI-скрытие кнопок через permissions;
- отдельные полномочия на изменение статуса там, где это имеет смысл.

## 15. Что нельзя переносить без проверки

Источник: спорные места, перечисленные выше.

Нельзя считать без дополнительной валидации:

- что `hr` и `defectoscopist` реально существуют как роли доступа;
- что `user` имеет осмысленный набор прав;
- что `shift.view_own` реально используется во всех ожидаемых сценариях;
- что `settings.*` и `permission.*` полностью внедрены;
- что `project_object.archive` уже поддержан полноценным жизненным циклом;
- что все даты и статусы оборудования можно трактовать одинаково;
- что вся модель доступа централизована и не содержит локальных исключений;
- что все UI-кнопки соответствуют реальным backend-проверкам.
