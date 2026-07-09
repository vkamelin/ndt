# Формы, отчеты, документы, файлы и справочники

## 1. Назначение отчета

Этот документ фиксирует, какие прикладные формы, отчеты, документы, файлы, вложения и справочники уже были предусмотрены в старой версии приложения.

Цель исследования:
- восстановить фактические пользовательские сценарии, видимые в интерфейсе;
- перечислить формы ввода и их поля;
- зафиксировать отчетные формы, журналы, экспорт и печатные шаблоны;
- описать файловую логику и таблицы вложений;
- собрать справочники и seed-данные, чтобы понять, что можно перенести в новую систему без домыслов.

Важно:
- в документ включены только подтвержденные элементы;
- если назначение формы или документа не удалось установить точно, это явно отмечено;
- если данных недостаточно, используется формулировка: `Недостаточно данных для точного вывода`.

## 2. Изученные источники

### Маршруты и контроллеры
- `routes/web.php`
- `app/Modules/Organizations/routes/web.php`
- `app/Modules/Documents/routes/web.php`
- `app/Modules/Equipment/routes/web.php`
- `app/Modules/Core/Http/Controllers/*`
- `app/Modules/Organizations/Controllers/*`
- `app/Modules/Documents/Http/Controllers/*`
- `app/Modules/Equipment/Http/Controllers/*`
- `app/Modules/Shifts/Http/Controllers/*`
- `app/Http/Controllers/FileDownloadController.php`
- `app/Http/Controllers/ExportExampleController.php`

### FormRequest и DTO
- `app/Modules/Core/Http/Requests/*`
- `app/Modules/Organizations/Http/Requests/*`
- `app/Modules/Documents/Http/Requests/*`
- `app/Modules/Equipment/Http/Requests/*`
- `app/Modules/Shifts/Http/Requests/*`
- `app/Modules/*/DTO/*`

### Миграции и модели
- `database/migrations/*`
- `app/Modules/*/Models/*`

### Frontend
- `resources/js/modules/Core/Pages/**`
- `resources/js/modules/Core/Components/**`
- `resources/js/modules/Organizations/Pages/**`
- `resources/js/modules/Documents/Pages/**`
- `resources/js/modules/Documents/Components/**`
- `resources/js/modules/Equipment/Pages/**`
- `resources/js/modules/Equipment/Components/**`
- `resources/js/modules/Shifts/Pages/**`
- `resources/js/modules/Shifts/Components/**`

### Экспорт и печать
- `app/Modules/Organizations/Services/OrganizationExportService.php`
- `app/Modules/Core/Services/EmployeeExportService.php`
- `app/Modules/Equipment/Services/EquipmentExportService.php`
- `app/Modules/Shifts/Exports/*`
- `app/Modules/Documents/Services/DocumentService.php`
- `app/Modules/Documents/DTO/*`

### Seed-данные и справочники
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/*Seeder.php`
- `app/Modules/*/Database/Seeders/*`

## 3. Формы ввода

### 3.1 Core: пользователи, роли, сотрудники, филиалы, объекты, должности

#### Форма: создание пользователя
- Где находится: `resources/js/modules/Core/Pages/Users/Create.vue` и связанная модалка/форма редактирования в разделе пользователей.
- Назначение: создание учетной записи для входа в систему.
- Сущность: `users`.
- Кто заполняет: администратор или пользователь с правом управления пользователями.
- Доступ: `user.create`, `user.update`, `user.view_any`.
- Поля:
  - `name`
  - `email`
  - `password`
  - `password_confirmation`
  - `status`
  - `roles[]`
- Обязательные поля:
  - `name`
  - `email`
  - `password`
  - `password_confirmation`
  - `status`
- Из справочников:
  - `roles[]` из `roles`
  - `status` из фиксированного списка `active|blocked`
- Валидация:
  - `email` уникален;
  - `password` подтверждается;
  - `roles` должны существовать в `permissions`-экосистеме Spatie.
- Ограничения и ошибки:
  - нельзя создать пользователя без email;
  - нельзя назначить несуществующую роль.
- Источник: `app/Modules/Core/Http/Requests/CreateUserRequest.php`, `app/Modules/Core/Http/Requests/UpdateUserRequest.php`, `app/Modules/Core/Http/Controllers/UserController.php`.

#### Форма: создание роли
- Где находится: раздел ролей, модальное окно на фронтенде.
- Назначение: создание RBAC-роли.
- Сущность: `roles`.
- Кто заполняет: администратор.
- Доступ: `role.create`, `role.update`, `role.view_any`.
- Поля:
  - `name`
  - `permissions[]`
- Обязательные поля:
  - `name`
- Из справочников:
  - `permissions[]` из таблицы `permissions`
- Валидация:
  - `name` уникально;
  - permissions должны существовать.
- Источник: `app/Modules/Core/Http/Requests/CreateRoleRequest.php`, `app/Modules/Core/Http/Requests/UpdateRoleRequest.php`, `app/Modules/Core/Http/Controllers/RoleController.php`.

#### Форма: сотрудник
- Где находится: `resources/js/modules/Core/Pages/Employees/Create.vue`, `Edit.vue`, `Show.vue`, `Components/EmployeeForm.vue`.
- Назначение: кадровая карточка сотрудника и вся связанная HR-информация.
- Сущность: `employees` + дочерние таблицы.
- Кто заполняет: HR, кадровик, администратор.
- Доступ: `employee.create`, `employee.update`, `employee.view_any`, `employee.view`, а также права на подформы (`employee_qualification.*`, `employee_medical_examination.*`, `employee_briefing.*`, `employee_ppe_item.*`, `employee_training.*`, `employee_document.*`).
- Поля основной карточки:
  - `user_id`
  - `last_name`
  - `first_name`
  - `middle_name`
  - `birth_date`
  - `phone`
  - `work_phone`
  - `work_email`
  - `email`
  - `snils`
  - `inn`
  - `passport_series`
  - `passport_number`
  - `passport_issued_by`
  - `passport_issued_at`
  - `passport_department_code`
  - `registration_address`
  - `actual_address`
  - `position_id`
  - `object_id`
  - `object_name`
  - `personnel_number`
  - `hired_at`
  - `fired_at`
  - `contract_type`
  - `work_type`
  - `salary`
  - `clothing_size`
  - `shoe_size`
  - `headgear_size`
  - `glove_size`
  - `respirator_size`
  - `status`
- Из справочников:
  - `user_id` из `users`
  - `position_id` из `positions`
  - `object_id` из `objects`
  - `status` из `active|vacation|sick_leave|maternity_leave|business_trip|terminated|suspended_hr`
  - `contract_type`, `work_type` и размерные поля вводятся как значения формы, но не подтверждены как внешние справочники
- Подформы:
  - квалификации;
  - медосмотры;
  - инструктажи;
  - СИЗ;
  - обучения;
  - кадровые документы.
- Источник: `app/Modules/Core/Http/Requests/CreateEmployeeRequest.php`, `app/Modules/Core/Http/Requests/UpdateEmployeeRequest.php`, `resources/js/modules/Core/Pages/Employees/Show.vue`, `app/Modules/Core/Http/Controllers/EmployeeController.php`.

#### Форма: филиал
- Где находится: `resources/js/modules/Core/Components/Branches/BranchCreateModal.vue`, редактирование через JSON-эндпоинт.
- Назначение: учет филиала и контактных данных.
- Сущность: `branches`.
- Кто заполняет: администратор, руководитель.
- Доступ: `branch.create`, `branch.update`, `branch.view_any`.
- Поля:
  - `name`
  - `address`
  - `responsible_employee_id`
  - `employee_ids[]`
  - `is_active`
  - `phones[]`
  - `emails[]`
- Обязательные поля:
  - `name`
  - `address`
- Из справочников:
  - `responsible_employee_id` из `employees`
  - `employee_ids[]` из `employees`
- Валидация:
  - `is_active` boolean;
  - телефоны и email-адреса передаются массивами объектов;
  - сотрудники должны существовать.
- Источник: `app/Modules/Core/Http/Requests/StoreBranchRequest.php`, `app/Modules/Core/Http/Controllers/BranchController.php`.

#### Форма: должность
- Где находится: раздел должностей.
- Назначение: справочник должностей.
- Сущность: `positions`.
- Кто заполняет: администратор/HR.
- Доступ: `position.create`, `position.update`, `position.view_any`.
- Поля:
  - `title`
  - `code`
  - `role`
  - `is_active`
  - `sort_order`
  - `tenant_id` при создании
- Обязательные поля:
  - `title`
  - `code`
- Из справочников:
  - `tenant_id` из `tenants` при создании
- Валидация:
  - `code` уникален;
  - `sort_order` integer >= 0;
  - `is_active` boolean.
- Источник: `app/Modules/Core/Http/Requests/StorePositionRequest.php`, `app/Modules/Core/Http/Controllers/PositionController.php`.

#### Форма: объект
- Где находится: `resources/js/modules/Core/Pages/Objects/Create.vue`, `Edit.vue`, форма объекта.
- Назначение: учет производственного объекта.
- Сущность: `objects`.
- Кто заполняет: администратор, производственный менеджер.
- Доступ: `project_object.create`, `project_object.update`, `project_object.view_any`.
- Поля:
  - `name`
  - `branch_id`
  - `address`
  - `date_start`
  - `date_end`
  - `operating_organization_id`
  - контактные данные
  - `responsible_employee_id`
  - `customer_ids[]`
  - `equipment_ids[]`
- Обязательные поля:
  - `name`
  - `branch_id`
  - `address`
  - `date_start`
  - `operating_organization_id`
- Из справочников:
  - `branch_id` из `branches`
  - `operating_organization_id` из `organizations`
  - `responsible_employee_id` из `employees`
  - `customer_ids[]` из `organizations`
  - `equipment_ids[]` из `equipment`
- Валидация:
  - `date_end` не раньше `date_start`;
  - customers не должны включать operating organization;
  - equipment должны существовать;
  - контактный блок хранится как отдельные поля.
- Источник: `app/Modules/Core/Http/Requests/ObjectStoreRequest.php`, `app/Modules/Core/Http/Controllers/ObjectController.php`.

### 3.2 Organizations

#### Форма: организация
- Где находится: `resources/js/modules/Organizations/Pages/Organizations/Index.vue`, `Create`/`Edit` модалки.
- Назначение: справочник контрагентов и реквизитов.
- Сущность: `organizations`.
- Кто заполняет: оператор справочника, бухгалтерия, менеджер.
- Доступ: `organization.create`, `organization.update`, `organization.view_any`, `organization.view`.
- Поля:
  - `name`
  - `full_name`
  - `inn`
  - `kpp`
  - `ogrn`
  - `okpo`
  - `legal_address`
  - `postal_address`
  - `actual_address`
  - `region`
  - `ceo_full_name`
  - `vat_status`
- Обязательные поля:
  - `name`
  - `inn`
  - `full_name`
- Из справочников:
  - `region` из `regions`
  - `vat_status` из `vat|no_vat`
- Валидация:
  - ИНН/КПП/ОГРН/ОКПО проходят форматные проверки;
  - `vat_status` ограничен enum;
  - адреса допускают пустые значения в части полей.
- Источник: `app/Modules/Organizations/Http/Requests/OrganizationStoreRequest.php`, `app/Modules/Organizations/Http/Requests/OrganizationUpdateRequest.php`, `app/Modules/Organizations/Controllers/OrganizationController.php`.

#### Форма: контакт организации
- Где находится: карточка организации.
- Назначение: хранение контактных лиц.
- Сущность: `organization_contacts`.
- Поля:
  - `last_name`
  - `first_name`
  - `middle_name`
  - `job_title`
  - `phone`
  - `email`
  - `is_primary`
  - `comment`
- Обязательные поля:
  - имя контакта
- Из справочников:
  - нет внешнего справочника, только boolean и текстовые поля
- Валидация:
  - `is_primary` boolean
- Источник: `app/Modules/Organizations/Http/Requests/ContactStoreRequest.php`, `app/Modules/Organizations/Controllers/OrganizationContactController.php`.

#### Форма: банковские реквизиты организации
- Где находится: карточка организации.
- Назначение: банковские данные.
- Сущность: `organization_bank_accounts`.
- Поля:
  - `bank_name`
  - `bik`
  - `account_number`
  - `correspondent_account`
  - `is_default`
- Обязательные поля:
  - `bank_name`
  - `bik`
  - `account_number`
- Из справочников:
  - нет
- Валидация:
  - `is_default` boolean
- Источник: `app/Modules/Organizations/Http/Requests/BankAccountStoreRequest.php`, `app/Modules/Organizations/Controllers/OrganizationBankAccountController.php`.

### 3.3 Documents

#### Форма: тип документа
- Где находится: `resources/js/modules/Documents/Pages/DocumentTypes/Index.vue`.
- Назначение: справочник типов документов.
- Сущность: `document_types`.
- Кто заполняет: администратор или делопроизводитель.
- Доступ: `document_type.create`, `document_type.update`, `document_type.view_any`.
- Поля:
  - `code`
  - `name`
  - `is_active`
  - `is_contract`
  - `description`
  - `sort_order`
- Обязательные поля:
  - `code`
  - `name`
- Валидация:
  - `name` уникален;
  - `sort_order` integer;
  - `is_active` и `is_contract` boolean.
- Источник: `app/Modules/Documents/Http/Requests/StoreDocumentTypeRequest.php`, `app/Modules/Documents/Http/Controllers/DocumentTypeController.php`.

#### Форма: документ
- Где находится: `resources/js/modules/Documents/Pages/Documents/Create.vue`, `Edit.vue`, `Show.vue`, `Components/Documents/DocumentForm.vue`.
- Назначение: единый реестр документов с версиями, связями и файлами.
- Сущность: `documents` + `document_files` + `document_versions` + `document_relations` + `document_tags`.
- Кто заполняет: делопроизводитель, ответственное лицо, администратор.
- Доступ: `document.create`, `document.update`, `document.view_any`, `document.view`.
- Поля:
  - `document_type_id`
  - `title`
  - `description`
  - `status`
  - `is_confidential`
  - `is_original_received`
  - `is_signed`
  - `requires_renewal`
  - `registration_number`
  - `document_number`
  - `document_date`
  - `effective_date`
  - `expiry_date`
  - `signed_at`
  - `issuer_organization_id`
  - `owner_type`
  - `owner_id`
  - `branch_id`
  - `organization_id`
  - `responsible_employee_id`
  - `issued_at`
  - `received_at`
  - `valid_from`
  - `valid_to`
  - `renewal_deadline`
  - `termination_date`
  - `archive_date`
  - `superseded_by_document_id`
  - `parent_document_id`
  - `revision_comment`
  - `tag_ids[]`
  - `relations[]`
  - `uploaded_files[]`
  - `uploaded_file_roles[]`
- Обязательные поля:
  - `document_type_id`
  - `title`
  - `status`
  - `is_confidential`
  - `requires_renewal`
  - часть формы owner/related blocks становится обязательной по условию, см. раздел валидации
- Из справочников:
  - `document_type_id` из `document_types`
  - `issuer_organization_id`, `organization_id` из `organizations`
  - `branch_id` из `branches`
  - `responsible_employee_id` из `employees`
  - `superseded_by_document_id`, `parent_document_id`, `related_document_id` из `documents`
  - `tag_ids[]` из `document_tags`
  - `relation_type` и `file_role` из фиксированных enum-значений
- Валидация:
  - `status` ограничен enum `draft|active|expired|terminated|archived|superseded|cancelled`;
  - `expiry_date >= effective_date`;
  - `valid_to >= valid_from`;
  - `owner_type` и `owner_id` должны быть заполнены вместе;
  - self-relation запрещена;
  - `superseded` требует `superseded_by_document_id`;
  - `uploaded_files.*` max 10240;
  - `uploaded_file_roles` из набора `primary|attachment|scan|signed_copy|generated`.
- Дополнительные данные:
  - у документа есть версии;
  - есть связи между документами;
  - есть теги;
  - есть признак конфиденциальности;
  - есть учет сканов и подписанных копий.
- Источник: `app/Modules/Documents/Http/Requests/CreateDocumentRequest.php`, `app/Modules/Documents/Http/Controllers/DocumentController.php`, `database/migrations/2026_03_09_000002_create_documents_table.php`, `database/migrations/*document_files*`, `database/migrations/*document_versions*`, `database/migrations/*document_relations*`, `database/migrations/*document_tags*`.

#### Форма: файл документа
- Где находится: карточка документа.
- Назначение: прикрепление файлов к документу.
- Сущность: `document_files`.
- Поля:
  - `file`
  - `file_role`
  - `file_storage_path`
  - `original_name`
  - `mime_type`
  - `file_size`
  - `version_no`
- Обязательные поля:
  - файл
  - роль файла
- Из справочников:
  - `file_role` из `primary|attachment|scan|signed_copy|generated`
- Валидация:
  - `file` max 10240;
  - `file_role` обязателен.
- Источник: `app/Modules/Documents/Http/Requests/CreateDocumentFileRequest.php`, `app/Modules/Documents/Services/DocumentService.php`, `database/migrations/*document_files*`.

#### Форма: связь документов
- Где находится: карточка документа.
- Назначение: связать документы между собой.
- Сущность: `document_relations`.
- Поля:
  - `related_document_id`
  - `relation_type`
- Из справочников:
  - `relation_type` из `attachment|reference|related|amendment`
- Валидация:
  - self-reference запрещен;
  - related document должен существовать.
- Источник: `app/Modules/Documents/Http/Requests/CreateDocumentRelationRequest.php`, `app/Modules/Documents/Http/Controllers/DocumentController.php`.

#### Форма: версия документа
- Где находится: карточка документа.
- Назначение: создание новой версии.
- Сущность: `document_versions`.
- Поля:
  - `title`
  - `status`
  - `revision_comment`
- Валидация:
  - `status` соответствует enum документа;
  - комментарий ревизии необязателен.
- Источник: `app/Modules/Documents/Http/Requests/CreateDocumentVersionRequest.php`, `app/Modules/Documents/Http/Controllers/DocumentController.php`.

### 3.4 Equipment

#### Форма: оборудование
- Где находится: `resources/js/modules/Equipment/Pages/Equipment/Index.vue`, `Show.vue`, `Create/Edit` формы.
- Назначение: карточка оборудования и жизненный цикл.
- Сущность: `equipment`.
- Кто заполняет: инженер, кладовщик, ответственный за оборудование.
- Доступ: `equipment.create`, `equipment.update`, `equipment.view_any`, `equipment.view`.
- Поля:
  - `name`
  - `equipment_type_id`
  - `status`
  - `inventory_number`
  - `condition`
  - `model`
  - `manufacturer`
  - `description`
  - `is_active`
  - `serial_number`
  - `passport_number`
  - `registration_number`
  - `barcode`
  - `qr_code`
  - `branch_id`
  - `commissioned_at`
  - `manufactured_at`
  - `purchased_at`
  - `service_life_until`
  - `last_used_at`
  - `usage_notes`
  - `requires_calibration`
  - `requires_verification`
  - `requires_attached_operator`
  - `can_be_assigned_to_project`
  - `verification_interval_days`
  - `last_verification_at`
  - `next_verification_at`
  - `verification_status`
  - `calibration_interval_days`
  - `last_calibration_at`
  - `next_calibration_at`
  - `calibration_status`
  - `metrology_notes`
  - `verification_document_file_id`
  - `calibration_document_file_id`
  - `responsible_employee_id`
  - `assigned_employee_id`
  - `issued_at`
  - `returned_at`
  - `responsibility_notes`
  - `last_maintenance_at`
  - `next_maintenance_at`
  - `repair_status`
  - `retired_at`
  - `write_off_reason`
  - `maintenance_notes`
- Обязательные поля:
  - `name`
  - `equipment_type_id`
  - `status`
  - `inventory_number`
  - `condition`
- Из справочников:
  - `equipment_type_id` из `equipment_types`
  - `branch_id` из `branches`
  - `responsible_employee_id`, `assigned_employee_id` из `employees`
  - `status` из `active|in_storage|under_maintenance|under_calibration|retired|written_off`
  - `condition` из `new|good|needs_attention|faulty|out_of_service`
  - `verification_status`, `calibration_status` из `valid|expiring_soon|expired|not_required`
  - `repair_status` из `none|planned|in_progress|completed`
- Валидация:
  - `inventory_number` уникален;
  - даты проверяются относительно друг друга;
  - `issued_at` требует `assigned_employee_id`;
  - часть полей зависит от статуса и признаков assignable/calibration/verification.
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentRequest.php`, `app/Modules/Equipment/Http/Requests/UpdateEquipmentRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentController.php`, `database/migrations/*equipment*`.

#### Форма: тип оборудования
- Где находится: справочник типов оборудования.
- Назначение: классификатор оборудования.
- Сущность: `equipment_types`.
- Поля:
  - `name`
  - `is_active`
  - `description`
  - `sort_order`
- Валидация:
  - `name` уникален;
  - `sort_order` integer.
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentTypeRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentTypeController.php`.

#### Форма: поверка оборудования
- Где находится: `equipment/equipment-verifications`.
- Назначение: журнал и карточка поверки.
- Сущность: `equipment_verifications`.
- Поля:
  - `equipment_id`
  - `verification_type`
  - `status`
  - `performed_at`
  - `valid_from`
  - `valid_until`
  - `next_verification_at`
  - `performed_by_org`
  - `performed_by_employee`
  - `certificate_number`
  - `result`
  - `notes`
  - `document_file_path`
- Из справочников:
  - `verification_type` из `primary|periodic|unscheduled|after_repair`
  - `status` из `scheduled|passed|failed|cancelled|expired`
  - `result` из `fit|unfit|limited_fit`
- Валидация:
  - дата логики внутри request;
  - файл необязателен, но поддерживается.
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentVerificationRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentVerificationController.php`, `database/migrations/*equipment_verifications*`.

#### Форма: калибровка оборудования
- Где находится: `equipment/equipment-calibrations`.
- Назначение: учет калибровки.
- Сущность: `equipment_calibrations`.
- Поля:
  - `equipment_id`
  - `calibration_type`
  - `status`
  - `performed_at`
  - `valid_from`
  - `valid_until`
  - `next_calibration_at`
  - `performed_by_org`
  - `performed_by_employee`
  - `certificate_number`
  - `reference_values`
  - `result`
  - `notes`
  - `document_file_path`
- Из справочников:
  - `calibration_type` из `primary|periodic|unscheduled|after_repair`
  - `status` из `scheduled|passed|failed|cancelled|expired`
  - `result` из `fit|unfit|limited_fit`
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentCalibrationRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentCalibrationController.php`, `database/migrations/*equipment_calibrations*`.

#### Форма: техническое обслуживание оборудования
- Где находится: `equipment/equipment-maintenances`.
- Назначение: ремонт, диагностика, профилактика и сервисные работы.
- Сущность: `equipment_maintenances`.
- Поля:
  - `equipment_id`
  - `maintenance_type`
  - `status`
  - `started_at`
  - `completed_at`
  - `next_maintenance_at`
  - `service_provider_type`
  - `service_provider_organization_id`
  - `service_provider_employee_id`
  - `cost_amount`
  - `downtime_days`
  - `description`
  - `result`
  - `notes`
  - `document_file_path`
- Из справочников:
  - `maintenance_type` из `inspection|preventive|repair|diagnostics|replacement|cleaning|other`
  - `status` из `planned|in_progress|completed|cancelled`
  - `service_provider_type` из `internal|external`
  - `result` из `restored|partially_restored|not_restored|prevented_failure`
- Валидация:
  - в зависимости от `service_provider_type` требуется либо организация, либо сотрудник;
  - стоимость и downtime неотрицательны.
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentMaintenanceRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentMaintenanceController.php`, `database/migrations/*equipment_maintenances*`.

#### Форма: закрепление оборудования
- Где находится: `equipment/equipment-assignments`.
- Назначение: выдача/закрепление оборудования за сотрудником или филиалом.
- Сущность: `equipment_assignments`.
- Поля:
  - `equipment_id`
  - `employee_id`
  - `branch_id`
  - `assigned_by_employee_id`
  - `issued_at`
  - `planned_return_at`
  - `returned_at`
  - `issue_reason`
  - `issue_condition`
  - `return_condition`
  - `status`
  - `notes`
  - `acceptance_document_file_path`
- Из справочников:
  - `issue_condition`, `return_condition` из `new|good|needs_attention|faulty|out_of_service`
  - `status` из `active|returned|lost|damaged|cancelled`
- Валидация:
  - обязательно хотя бы одно из `employee_id` или `branch_id`;
  - при создании требуется `issued_at`;
  - документ может быть приложен.
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentAssignmentRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentAssignmentController.php`, `database/migrations/*equipment_assignments*`.

#### Форма: перемещение оборудования
- Где находится: `equipment/equipment-movements`.
- Назначение: учет перемещений между объектами, на поверку, в ремонт, на списание.
- Сущность: `equipment_movements`.
- Поля:
  - `equipment_id`
  - `movement_type`
  - `from_branch_id`
  - `to_branch_id`
  - `moved_by_employee_id`
  - `responsible_employee_id`
  - `moved_at`
  - `status`
  - `transport_info`
  - `notes`
  - `document_file_path`
- Из справочников:
  - `movement_type` из `transfer|issue_to_site|return_to_storage|send_for_verification|send_for_calibration|send_for_repair|write_off|other`
  - `status` из `planned|completed|cancelled`
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentMovementRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentMovementController.php`, `database/migrations/*equipment_movements*`.

#### Форма: дефект оборудования
- Где находится: `equipment/equipment-defects`.
- Назначение: регистрация дефектов и их устранения.
- Сущность: `equipment_defects`.
- Поля:
  - `equipment_id`
  - `detected_at`
  - `reported_by_employee_id`
  - `defect_type`
  - `severity`
  - `status`
  - `title`
  - `description`
  - `impact_on_operation`
  - `maintenance_id`
  - `resolved_at`
  - `resolution_notes`
  - `document_file_path`
- Из справочников:
  - `defect_type` из `mechanical|electrical|measurement_error|calibration_issue|verification_issue|damage|missing_part|other`
  - `severity` из `low|medium|high|critical`
  - `status` из `open|in_review|in_repair|resolved|closed|cancelled`
  - `impact_on_operation` из `none|limited_use|operation_prohibited`
- Валидация:
  - `resolved_at` требуется для статусов resolved/closed;
  - `resolved_at >= detected_at`.
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentDefectRequest.php`, `app/Modules/Equipment/Http/Controllers/EquipmentDefectController.php`, `database/migrations/*equipment_defects*`.

#### Форма: документ оборудования
- Где находится: `equipment/equipment-documents`.
- Назначение: хранение паспорта, актов и фотографий оборудования.
- Сущность: `equipment_documents`.
- Поля:
  - `equipment_id`
  - `document_type`
  - `title`
  - `document_number`
  - `issued_at`
  - `valid_until`
  - `file_path`
  - `is_primary`
  - `notes`
- Из справочников:
  - `document_type` из `passport|verification_certificate|calibration_certificate|maintenance_act|repair_act|photo|manual|write_off_act|other`
- Валидация:
  - файл обязателен;
  - один и тот же тип документа может иметь признак primary, в БД есть ограничение на первичность.
- Источник: `app/Modules/Equipment/Http/Controllers/EquipmentDocumentController.php`, `database/migrations/*equipment_documents*`.

### 3.5 Shifts, журналы и отчеты смен

#### Форма: старт смены
- Где находится: `Shifts` dashboard и модальное окно старта смены.
- Назначение: открыть смену лаборанта или расшифровщика.
- Сущность: `shifts`.
- Кто заполняет: текущий пользователь в роли сменного сотрудника.
- Доступ: `shift.start`.
- Поля:
  - явных пользовательских полей почти нет;
  - форма управляется текущим объектом, пользователем и временем.
- Ограничения:
  - форма минимальная, без пользовательских данных;
  - для некоторых ролей может быть запрещено передавать комментарий.
- Источник: `app/Modules/Shifts/Http/Requests/StartLaborantShiftRequest.php`, `app/Modules/Shifts/Http/Controllers/ShiftController.php`.

#### Форма: завершение смены лаборанта
- Где находится: `resources/js/modules/Shifts/Components/Shifts/ShiftFinishModal.vue` и связанный backend.
- Назначение: закрыть смену и зафиксировать состояние оборудования и рабочего места.
- Сущность: `shifts`.
- Кто заполняет: лаборант.
- Доступ: `shift.finish`.
- Поля:
  - `machine_condition_status`
  - `machine_condition_comment`
  - `workplace_cleaned`
  - `workplace_cleaned_comment`
  - чеклист ежедневного обслуживания
  - чеклист еженедельного обслуживания
  - чеклист ежемесячного обслуживания
  - `comment`
  - `notes`
- Валидация:
  - для лаборанта поля обязательны;
  - для роли расшифровщика эти поля не используются;
  - значения для состояния машины ограничены `ok|issue|fault`.
- Источник: `app/Modules/Shifts/Http/Requests/FinishLaborantShiftRequest.php`, `app/Modules/Shifts/Http/Controllers/ShiftController.php`.

#### Форма: регламентные работы смены
- Где находится: `Shift maintenance state/save`.
- Назначение: отметить ежедневные, еженедельные и ежемесячные работы.
- Сущность: часть `shifts` или связанная структура смены.
- Поля:
  - набор boolean-флагов по операциям обслуживания;
  - `comment`.
- Источник: `app/Modules/Shifts/Http/Requests/SaveShiftMaintenanceRequest.php`, `app/Modules/Shifts/Http/Controllers/ShiftController.php`.

#### Форма: запрос плёнки
- Где находится: `resources/js/modules/Shifts/Components/FilmRequests/*`.
- Назначение: запросить плёнку по типам и количеству.
- Сущность: бизнес-запрос/уведомление, связанное со сменой.
- Поля:
  - `film_type_ids[]`
  - `quantities_by_film_type[]`
- Из справочников:
  - `film_types`
- Валидация:
  - все количества > 0;
  - типы плёнки должны быть активны и не удалены.
- Источник: `app/Modules/Shifts/Http/Requests/StoreFilmRequestRequest.php`, `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php`.

#### Форма: поступление плёнки
- Где находится: журнал поступления плёнки.
- Назначение: приход плёнки на склад.
- Поля:
  - `film_type_id`
  - `quantity_meters`
  - `received_from_employee_id`
  - `comment`
- Источник: `app/Modules/Shifts/Http/Requests/StoreFilmReceiptRequest.php`, `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php`.

#### Форма: выдача плёнки
- Где находится: журнал выдачи плёнки.
- Назначение: списание/выдача плёнки в смену.
- Поля:
  - `issued_to_employee_id`
  - `film_type_id`
  - `quantity_meters`
  - `comment`
- Источник: `app/Modules/Shifts/Http/Requests/StoreFilmIssueRequest.php`, `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php`.

#### Форма: запрос химии
- Где находится: `ChemicalRequests`.
- Назначение: запросить химические реагенты.
- Поля:
  - `chemical_types[]`
  - `quantities_by_chemical_type[]`
- Из справочников:
  - enum `DevelopingChemicalType`
- Источник: `app/Modules/Shifts/Http/Requests/StoreChemicalRequestRequest.php`, `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php`.

#### Форма: поступление химии
- Где находится: журнал химии.
- Поля:
  - `chemical_type`
  - `quantity_canisters`
  - `comment`
- Источник: `app/Modules/Shifts/Http/Requests/StoreChemicalReceiptRequest.php`, `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php`.

#### Форма: замена химии
- Где находится: модалка замены химии.
- Назначение: смена химии в проявочной машине.
- Поля:
  - `developing_machine_id`
  - `quantities[]`
  - `changed_at`
- Ограничения:
  - машина должна быть оборудованием текущего объекта;
  - тип оборудования должен быть `Проявочная машина`;
  - хотя бы одно количество должно быть больше 0.
- Источник: `app/Modules/Shifts/Http/Requests/StoreChemicalReplacementRequest.php`, `app/Modules/Shifts/Http/Controllers/LaborantShiftController.php`.

#### Форма: завершение запроса химии
- Где находится: журнал запросов химии.
- Назначение: отметить, что химия получена.
- Поля:
  - `received_quantity_canisters`
  - `received_at`
- Источник: `app/Modules/Shifts/Http/Requests/CompleteChemicalRequestRequest.php`, `app/Modules/Shifts/Http/Controllers/ChemicalRequestController.php`.

#### Форма: просмотренная плёнка
- Где находится: `DecoderShiftFilmGroups`.
- Назначение: учет групп просмотренной плёнки.
- Поля:
  - `senior_ndt_employee_id`
  - `film_size_meters`
  - `exposures_per_joint`
  - `joints_count`
- Источник: `app/Modules/Shifts/Http/Requests/StoreDecoderShiftFilmGroupRequest.php`, `app/Modules/Shifts/Http/Controllers/DecoderShiftFilmGroupController.php`.

#### Форма: брак
- Где находится: `DecoderShiftRejects`.
- Назначение: учет брака.
- Поля:
  - `senior_ndt_employee_id`
  - `reject_category`
  - `reject_reason`
  - `film_size_meters`
  - `rejected_films_count`
  - `rejected_joints_count`
  - `comment`
- Источник: `app/Modules/Shifts/Http/Requests/StoreDecoderShiftRejectRequest.php`, `app/Modules/Shifts/Http/Controllers/DecoderShiftRejectController.php`.

#### Форма: подозрение на подлог
- Где находится: `DecoderShiftForgerySuspicions`.
- Назначение: отметка подозрения на подлог.
- Поля:
  - `has_suspicion`
  - `description`
- Валидация:
  - `description` обязателен только если `has_suspicion = 1`.
- Источник: `app/Modules/Shifts/Http/Requests/SaveDecoderShiftForgerySuspicionRequest.php`, `app/Modules/Shifts/Http/Controllers/DecoderShiftForgerySuspicionController.php`.

#### Форма: очистка рабочего места
- Где находится: `DecoderShiftCleanup`.
- Назначение: фиксация уборки после смены.
- Поля:
  - `is_completed`
  - `comment`
- Источник: `app/Modules/Shifts/Http/Requests/SaveDecoderShiftCleanupRequest.php`, `app/Modules/Shifts/Http/Controllers/DecoderShiftController.php`.

#### Форма: дешифровка
- Где находится: `DecoderShiftDecryptions`.
- Назначение: регистрация результата расшифровки.
- Поля:
  - `brought_by_employee_id`
  - `joint_number`
  - `pipe_diameter_mm`
  - `wall_thickness_mm`
  - `rs_type_id`
  - `is_acceptable`
  - `defect`
- Из справочников:
  - `rs_type_id` из `rs_types`
- Валидация:
  - `rs_type_id` nullable;
  - числовые поля положительные.
- Источник: `app/Modules/Shifts/Http/Requests/StoreDecoderShiftDecryptionRequest.php`, `app/Modules/Shifts/Http/Controllers/DecoderShiftDecryptionJournalController.php`.

## 4. Валидация форм

### 4.1 Общие закономерности
- Для большинства сущностей используется связка `FormRequest` + DTO + сервис.
- Валидация опирается на реальные таблицы и enum-значения.
- Для связанных сущностей почти везде есть `exists` на FK.
- Для дат в нескольких формах есть взаимные проверки (`after_or_equal`, зависимость от статуса).
- Для файлов используется единый предел размера: `max:10240`.

### 4.2 Пользователи и роли
- `CreateUserRequest`:
  - `email` уникален;
  - `password` подтверждается;
  - `roles[]` должны существовать.
- `UpdateUserRequest`:
  - пароль необязателен;
  - роль/статус сохраняются по тем же правилам.
- `CreateRoleRequest` / `UpdateRoleRequest`:
  - `name` уникально;
  - permissions должны существовать.

### 4.3 Сотрудники
- Статус сотрудника ограничен перечислением:
  - `active`
  - `vacation`
  - `sick_leave`
  - `maternity_leave`
  - `business_trip`
  - `terminated`
  - `suspended_hr`
- Дочерние формы:
  - квалификация;
  - медосмотр;
  - инструктаж;
  - СИЗ;
  - обучение;
  - документ.
- Часть полей чувствительна к формату и типу хранения в БД.

### 4.4 Организации
- `vat_status` ограничен `vat|no_vat`.
- `region` должен существовать.

### 4.5 Документы
- `status` строго enum.
- `owner_type` и `owner_id` нужны вместе.
- `superseded` требует документа-заместителя.
- `expiry_date` и `valid_to` контролируются относительно начала периода.
- `uploaded_files.*` ограничены размером.
- `relation_type` и `file_role` ограничены enum.

### 4.6 Оборудование
- Статусы, состояния и признаки assignability/calibration/verification ограничены enum/boolean.
- `inventory_number` уникален.
- По нескольким полям есть логические зависимости:
  - выдача требует сотрудника;
  - сервисный тип провайдера определяет, нужна ли организация или сотрудник;
  - дата завершения не может быть раньше начала.

### 4.7 Смены
- Запросы плёнки, химии, брака и дешифровки используют ограниченные enum и обязательные числа > 0.
- Форма завершения смены содержит обязательный чеклист и комментарии.

## 5. Отчеты

### 5.1 Отчеты смен лаборантов
- Название: `Отчеты смен лаборантов`.
- Где находится: `resources/js/modules/Shifts/Pages/LabShiftReports/Index.vue`, `Show.vue`.
- Назначение: сводный отчет по смене лаборанта.
- Использует:
  - смены;
  - поступление плёнки;
  - поступление химии;
  - выдачу плёнки;
  - регламентные работы;
  - состояние машины и рабочего места.
- Кто формирует: система автоматически по данным смены.
- Кто смотрит: лаборант, руководитель, администратор.
- Кто утверждает/закрывает: фактически смена закрывается при завершении; отдельного этапа утверждения не найдено.
- Печатная форма: `Недостаточно данных для точного вывода`.
- Экспорт: отдельный Excel-export для основного журнала не найден, но страницы отчетов существуют.
- Источник: `app/Modules/Shifts/Http/Controllers/LabShiftReportController.php`, `resources/js/modules/Shifts/Pages/LabShiftReports/Index.vue`, `resources/js/modules/Shifts/Pages/LabShiftReports/Show.vue`.

### 5.2 Отчеты смен расшифровщиков
- Название: `Отчеты смен расшифровщиков`.
- Где находится: `resources/js/modules/Shifts/Pages/DecoderShiftReports/Index.vue`, `Show.vue`.
- Назначение: сводный отчет по смене расшифровщика.
- Использует:
  - группы просмотренной плёнки;
  - брак;
  - подлог;
  - очистку рабочего места;
  - дешифровки;
  - итоговую статистику по звеньям.
- Кто формирует: система на основе сменных операций.
- Кто смотрит: расшифровщик, руководитель, администратор.
- Кто утверждает/закрывает: явного шага утверждения не обнаружено.
- Печатная форма: `Недостаточно данных для точного вывода`.
- Источник: `app/Modules/Shifts/Http/Controllers/DecoderShiftReportController.php`, `resources/js/modules/Shifts/Pages/DecoderShiftReports/Index.vue`, `resources/js/modules/Shifts/Pages/DecoderShiftReports/Show.vue`.

### 5.3 Журнал поступления и списания плёнки
- Название: `Журнал поступления и списания плёнки`.
- Где находится: `resources/js/modules/Shifts/Pages/FilmInventoryTransactions/Index.vue`.
- Назначение: журнал движений плёнки.
- Использует:
  - дата;
  - сотрудник;
  - объект;
  - смена;
  - тип плёнки;
  - направление движения;
  - тип операции;
  - количество;
  - комментарий.
- Экспорт: Excel.
- Источник: `app/Modules/Shifts/Http/Controllers/FilmInventoryTransactionController.php`, `app/Modules/Shifts/Exports/FilmInventoryJournalExport.php`.

### 5.4 Журнал поступления и списания химии
- Название: `Журнал поступления и списания химии`.
- Где находится: `resources/js/modules/Shifts/Pages/DevelopingChemicalTransactions/Index.vue`.
- Назначение: журнал движений химии.
- Использует:
  - дата;
  - сотрудник;
  - объект;
  - смена;
  - тип химии;
  - направление;
  - тип операции;
  - количество;
  - комментарий.
- Экспорт: Excel.
- Источник: `app/Modules/Shifts/Http/Controllers/DevelopingChemicalTransactionController.php`, `app/Modules/Shifts/Exports/DevelopingChemicalJournalExport.php`.

### 5.5 Журнал запросов химии
- Название: `Журнал запросов химии`.
- Где находится: `resources/js/modules/Shifts/Pages/ChemicalRequests/Index.vue`.
- Назначение: отслеживание запросов на химию.
- Использует:
  - смену;
  - объект;
  - инициатора;
  - тип химии;
  - запрошенный объем;
  - статус;
  - дату запроса;
  - дату закрытия;
  - полученный объем;
  - дату получения.
- Экспорт: Excel.
- Дополнительно: есть modal закрытия запроса.
- Источник: `app/Modules/Shifts/Http/Controllers/ChemicalRequestController.php`, `app/Modules/Shifts/Exports/ChemicalRequestJournalExport.php`, `resources/js/modules/Shifts/Components/ChemicalRequests/ChemicalRequestCompleteModal.vue`.

### 5.6 Журнал просмотренной плёнки
- Название: `Журнал просмотренной плёнки`.
- Где находится: `resources/js/modules/Shifts/Pages/DecoderShiftFilmGroups/Index.vue`.
- Назначение: учет групп просмотренной пленки.
- Экспорт: Excel.
- Источник: `app/Modules/Shifts/Http/Controllers/DecoderShiftFilmGroupController.php`, `app/Modules/Shifts/Exports/DecoderShiftFilmGroupExport.php`.

### 5.7 Журнал брака
- Название: `Журнал брака`.
- Где находится: `resources/js/modules/Shifts/Pages/DecoderShiftRejects/Index.vue`.
- Назначение: учет брака.
- Экспорт: Excel.
- Источник: `app/Modules/Shifts/Http/Controllers/DecoderShiftRejectController.php`, `app/Modules/Shifts/Exports/DecoderShiftRejectExport.php`.

### 5.8 Журнал подлога
- Название: `Журнал подлога`.
- Где находится: `resources/js/modules/Shifts/Pages/DecoderShiftForgerySuspicions/Index.vue`.
- Назначение: фиксация подозрений на подлог.
- Экспорт: Excel.
- Источник: `app/Modules/Shifts/Http/Controllers/DecoderShiftForgerySuspicionController.php`, `app/Modules/Shifts/Exports/DecoderShiftForgerySuspicionExport.php`.

### 5.9 Журнал учета дешифровки пленки
- Название: `Журнал учета дешифровки пленки`.
- Где находится: `resources/js/modules/Shifts/Pages/DecoderShiftDecryptions/Index.vue`.
- Назначение: журнал дешифровок.
- Экспорт: Excel.
- Источник: `app/Modules/Shifts/Http/Controllers/DecoderShiftDecryptionJournalController.php`, `app/Modules/Shifts/Exports/DecoderShiftDecryptionJournalExport.php`.

### 5.10 Мои отчеты
- Название: `Мои отчеты`.
- Где находится: `resources/js/modules/Shifts/Pages/MyReports/Index.vue`.
- Назначение: персональная точка входа в отчеты по ролям.
- Кто смотрит: текущий пользователь.
- Источник: `app/Modules/Shifts/Http/Controllers/MyReportController.php`.

## 6. Документы и печатные формы

### 6.1 Реестр документов
- Документальная сущность: `documents`.
- Печатные/просмотровые элементы:
  - карточка документа;
  - вкладки `Основные данные`, `Реквизиты`, `Привязка`, `Сроки и контроль`, `Связанные данные`;
  - список файлов;
  - список версий;
  - список связей.
- Источник: `resources/js/modules/Documents/Pages/Documents/Show.vue`, `app/Modules/Documents/Http/Controllers/DocumentController.php`.

### 6.2 Реестр типов документов
- Справочник типов документов с CRUD.
- Источник: `resources/js/modules/Documents/Pages/DocumentTypes/Index.vue`, `app/Modules/Documents/Http/Controllers/DocumentTypeController.php`.

### 6.3 Печатные формы организаций
- Есть PDF-печать карточки организации.
- Источник: `app/Modules/Organizations/Controllers/OrganizationController.php`, маршрут `GET /organizations/{organization}/print`.

### 6.4 Печатные формы сотрудников
- В карточке сотрудника есть печать.
- Источник: `resources/js/modules/Core/Pages/Employees/Edit.vue`, `resources/js/modules/Core/Pages/Employees/Show.vue`, маршрут `GET /employees/{employee}/print`.

### 6.5 Технические демо-шаблоны
- В старой версии есть демонстрационные export/print-примеры:
  - `Реестр заявок РК`;
  - `Сотрудники`;
  - PDF-акт;
  - PDF-реестр сотрудников.
- Важное уточнение:
  - это демонстрационные шаблоны, а не обязательно прикладные бизнес-документы компании.
- Источник: `app/Http/Controllers/ExportExampleController.php`, маршруты `/exports/examples/*`.

## 7. Экспорт данных

### 7.1 Организации
- Экспорт: Excel.
- Название файла/отчета: `Список организаций`.
- Колонки:
  - ID;
  - Название;
  - Краткое название;
  - ИНН;
  - КПП;
  - ОГРН;
  - Телефон;
  - Email;
  - Юр. адрес;
  - Факт. адрес;
  - Контактное лицо;
  - Телефон контактного лица;
  - Активна;
  - Создана;
  - Обновлена.
- Источник: `app/Modules/Organizations/Services/OrganizationExportService.php`.

### 7.2 Сотрудники
- Экспорт: Excel.
- Источник: `app/Modules/Core/Services/EmployeeExportService.php`, `app/Modules/Core/Http/Requests/EmployeeExportRequest.php`.
- Поддерживаемые поля экспорта:
  - ФИО и части ФИО;
  - дата рождения;
  - телефоны;
  - email;
  - SNILS/INN;
  - табельный номер;
  - даты найма/увольнения;
  - статус;
  - тип договора;
  - тип работы;
  - зарплата;
  - должность;
  - филиал;
  - объект;
  - email пользователя.

### 7.3 Оборудование
- Экспорт: Excel.
- Источник: `app/Modules/Equipment/Services/EquipmentExportService.php`, `app/Modules/Equipment/Http/Requests/EquipmentExportRequest.php`.
- Колонки:
  - ID;
  - наименование;
  - инвентарный номер;
  - серийный номер;
  - модель;
  - производитель;
  - статус;
  - состояние;
  - тип оборудования;
  - филиал;
  - ответственный сотрудник;
  - признаки поверки/калибровки;
  - статусы поверки/калибровки;
  - даты commissioning и контрольных проверок;
  - дата создания.

### 7.4 Журналы смен
- Плёнка: Excel.
- Химия: Excel.
- Запросы химии: Excel.
- Группы просмотренной плёнки: Excel.
- Брак: Excel.
- Подлог: Excel.
- Дешифровка: Excel.
- Источники:
  - `app/Modules/Shifts/Exports/FilmInventoryJournalExport.php`
  - `app/Modules/Shifts/Exports/DevelopingChemicalJournalExport.php`
  - `app/Modules/Shifts/Exports/ChemicalRequestJournalExport.php`
  - `app/Modules/Shifts/Exports/DecoderShiftFilmGroupExport.php`
  - `app/Modules/Shifts/Exports/DecoderShiftRejectExport.php`
  - `app/Modules/Shifts/Exports/DecoderShiftForgerySuspicionExport.php`
  - `app/Modules/Shifts/Exports/DecoderShiftDecryptionJournalExport.php`

### 7.5 Демонстрационные экспортные примеры
- `Реестр заявок РК` - демо Excel.
- `Сотрудники` - демо Excel.
- `PDF-акт` - демо PDF.
- `PDF-реестр сотрудников` - демо PDF.
- Источник: `app/Http/Controllers/ExportExampleController.php`.

## 8. Файлы и вложения

### 8.1 Общий механизм хранения
- Для загрузки и выдачи публичной ссылки в старой версии используется только `app/Services/FileStorageService.php`.
- Файлы хранятся в зашифрованном виде.
- Доступ идет через signed route `files.show`.
- Ссылка создается через `getPublicUrl()`.
- Поток выдачи файла:
  - загрузка через `storeUploadedFile()`;
  - сохранение метаданных;
  - получение signed URL;
  - скачивание через `FileDownloadController`.
- Источник: `app/Services/FileStorageService.php`, `app/Http/Controllers/FileDownloadController.php`, маршрут `GET /files/{token}`.

### 8.2 Вложения сотрудников
- Типы файлов:
  - документы сотрудника;
  - сканы медосмотров.
- Ограничения:
  - `mimes: jpg, jpeg, png, webp, pdf, doc, docx, odt`
  - `max: 10240`
- Префикты хранения:
  - `employee-documents`
  - `employee-medical-examinations`
- Таблицы:
  - `employee_documents.file_path`
  - `employee_medical_examinations.scan_path`
- Источник: `app/Modules/Core/Http/Controllers/EmployeeController.php`, `database/migrations/*employee_documents*`, `database/migrations/*employee_medical_examinations*`.

### 8.3 Вложения оборудования
- Типы файлов:
  - документы калибровки;
  - документы поверки;
  - документы обслуживания;
  - документы закрепления;
  - документы перемещения;
  - документы дефектов;
  - общие документы оборудования.
- Ограничения:
  - `mimes: jpg, jpeg, png, webp, pdf, doc, docx, odt`
  - для общих документов оборудования дополнительно допускаются `xls, xlsx`
  - `max: 10240`
- Префиксы хранения:
  - `equipment-calibration-documents`
  - `equipment-verification-documents`
  - `equipment-maintenance-documents`
  - `equipment-assignment-documents`
  - `equipment-movement-documents`
  - `equipment-defect-documents`
  - `equipment-documents`
- Таблицы/поля:
  - `equipment_verifications.document_file_path`
  - `equipment_calibrations.document_file_path`
  - `equipment_maintenances.document_file_path`
  - `equipment_assignments.acceptance_document_file_path`
  - `equipment_movements.document_file_path`
  - `equipment_defects.document_file_path`
  - `equipment_documents.file_path`
- Источник: `app/Modules/Equipment/Http/Controllers/*`, `database/migrations/*equipment_*`.

### 8.4 Вложения документов
- Таблица `document_files` содержит:
  - `document_id`
  - `file_storage_path`
  - `original_name`
  - `mime_type`
  - `file_size`
  - `file_role`
  - `version_no`
  - `uploaded_by`
- Роли файла:
  - `primary`
  - `attachment`
  - `scan`
  - `signed_copy`
  - `generated`
- Источник: `database/migrations/*document_files*`, `app/Modules/Documents/Services/DocumentService.php`.

### 8.5 Кто может загружать и удалять
- Сотрудники:
  - загрузка документов и сканов через HR-карточку.
- Оборудование:
  - загрузка документов через соответствующие карточки жизненного цикла оборудования.
- Документы:
  - добавление файла, связи, версии через карточку документа.
- Удаление:
  - конкретные операции удаления файла/связи/записи есть у контроллеров документов и оборудования.
- Источник:
  - `app/Modules/Core/Http/Controllers/EmployeeController.php`
  - `app/Modules/Equipment/Http/Controllers/*`
  - `app/Modules/Documents/Http/Controllers/DocumentController.php`

### 8.6 Версии файлов
- Для документов предусмотрена версионность:
  - `document_versions`
  - `document_files.version_no`
  - `documents.version_no`
- Для остальных сущностей версионность файлов как отдельная доменная функция не подтверждена.
- Источник: `database/migrations/*document_versions*`, `database/migrations/*document_files*`, `database/migrations/2026_03_09_000002_create_documents_table.php`.

## 9. Архив

### 9.1 Документы
- Архивирование встроено в саму сущность документа:
  - `status = archived`
  - `archive_date`
  - soft deletes
  - `deleted_by`
- Источник: `database/migrations/2026_03_09_000002_create_documents_table.php`.

### 9.2 Оборудование
- Архивность/вывод из эксплуатации отражается через:
  - `status`
  - `retired_at`
  - `write_off_reason`
  - `repair_status`
  - soft deletes
- Источник: `database/migrations/*equipment*`.

### 9.3 Организации и сущности справочников
- Организации, сотрудники, позиции, филиалы и другие справочники используют soft delete.
- Это позволяет скрывать записи, не разрушая связи.
- Источник: соответствующие миграции таблиц `organizations`, `employees`, `branches`, `positions`, `document_types`, `equipment_types`.

### 9.4 Сменные данные
- Для сменных журналов явного архива как отдельного состояния не найдено.
- Источники удаления/истории неочевидны.
- `Недостаточно данных для точного вывода`.

## 10. Справочники

### 10.1 Роли и разрешения
- Хранятся в `roles` и `permissions`.
- Используются для:
  - доступа к страницам;
  - отображения меню;
  - backend-authorize;
  - контроля отчетов и экспортов.
- Источник: `database/seeders/PermissionsSeeder.php`, `DocumentPermissionsSeeder.php`, `EquipmentPermissionsSeeder.php`, `LaborantShiftPermissionsSeeder.php`, `DecoderShiftPermissionsSeeder.php`.

### 10.2 Должности
- Таблица: `positions`.
- Поля:
  - `title`
  - `code`
  - `role`
  - `is_active`
  - `sort_order`
- Используется в форме сотрудника и в seed-данных.
- Источник: `database/seeders/PositionSeeder.php`.

### 10.3 Регионы
- Таблица: `regions`.
- Поля:
  - `code`
  - `name`
  - `is_active`
  - `sort_order`
- Используется в форме организации.
- Источник: `database/seeders/RegionSeeder.php`.

### 10.4 Типы плёнки
- Таблица: `film_types`.
- Поля:
  - `code`
  - `name`
  - `is_active`
- Используется в сменных формах и журналах.
- Источник: `database/seeders/FilmTypeSeeder.php`.

### 10.5 Типы документов
- Таблица: `document_types`.
- Используются в форме документа и в карточках.
- Источник: `app/Modules/Documents/Http/Requests/StoreDocumentTypeRequest.php`, `database/migrations/*document_types*`.

### 10.6 Типы оборудования
- Таблица: `equipment_types`.
- Используются в форме оборудования и журнале оборудования.
- Источник: `app/Modules/Equipment/Http/Requests/StoreEquipmentTypeRequest.php`, `database/migrations/*equipment_types*`.

### 10.7 RS types
- Таблица: `rs_types`.
- Используются в форме дешифровки.
- Источник: `database/migrations/*rs_types*`, `app/Modules/Shifts/Http/Requests/StoreDecoderShiftDecryptionRequest.php`.

### 10.8 Справочники внутри enum-значений
- `employee.status`
- `organization.vat_status`
- `document.status`
- `document.file_role`
- `equipment.status`
- `equipment.condition`
- `equipment.verification_status`
- `equipment.calibration_status`
- `equipment.repair_status`
- `equipment.defect_type`
- `equipment.maintenance_type`
- `shifts.*` и журнальные enum-значения смен
- `chemical request / film / decoder` enum-значения
- Источник: соответствующие FormRequest и миграции.

## 11. Seed-данные

### 11.1 Что заполняется автоматически
- `DatabaseSeeder` создаёт:
  - базовые роли;
  - базового администратора;
  - лаборанта;
  - набор permission-сидеров;
  - должности;
  - регионы;
  - типы плёнки.
- Источник: `database/seeders/DatabaseSeeder.php`.

### 11.2 Должности из seed
- `Генеральный директор`
- `HR-менеджер`
- `Дефектоскопист`
- `Лаборант`
- Источник: `database/seeders/PositionSeeder.php`.

### 11.3 Типы плёнки из seed
- `D4`
- `D7`
- `OTHER` (`Другое`)
- Источник: `database/seeders/FilmTypeSeeder.php`.

### 11.4 Основные seeders permissions
- `PermissionsSeeder`
- `DocumentPermissionsSeeder`
- `EquipmentPermissionsSeeder`
- `LaborantShiftPermissionsSeeder`
- `DecoderShiftPermissionsSeeder`
- Важное наблюдение:
  - permissions создаются централизованно;
  - seeders синхронизируют разрешения с ролями;
  - descriptions у permissions есть.

### 11.5 Регионы
- В seed-данных присутствует полный список российских регионов.
- Точный полный перечень лучше брать непосредственно из `database/seeders/RegionSeeder.php`.
- Источник: `database/seeders/RegionSeeder.php`.

## 12. Незавершенные формы, отчеты и документы

### 12.1 Недостаточно данных для точного вывода
- Полная структура некоторых update-request и модалок не была прочитана построчно:
  - часть update-классов документов;
  - часть вспомогательных модалок смен;
  - часть внутренних view-model DTO.
- Это не означает, что они отсутствуют, только что их точная структура не зафиксирована в этом исследовании.

### 12.2 Неясные элементы
- Для некоторых отчетов не найден отдельный этап утверждения.
- Для части печатных форм не найден отдельный PDF-генератор в классическом виде, только экран отчета.
- Для отдельных файловых полей на уровне сущности таблица и request есть, но отдельный upload endpoint не выделен.

### 12.3 Демо-экспорты
- `ExportExampleController` выглядит как демонстрационный/примерный функционал.
- Его нельзя автоматически считать бизнес-требованием без подтверждения заказчика.

## 13. Что можно использовать в новой системе

### 13.1 Можно переносить почти без изменений
- Реестр типов документов.
- Реестр организаций и реквизитов.
- Реестр оборудования и его жизненный цикл.
- Файловую модель документов с версиями, связями и ролями файлов.
- Журналы смен:
  - плёнка;
  - химия;
  - запросы химии;
  - брак;
  - подлог;
  - дешифровка;
  - просмотренная пленка.
- HR-формы:
  - сотрудники;
  - медосмотры;
  - инструктажи;
  - СИЗ;
  - обучение;
  - кадровые документы.

### 13.2 Можно использовать как основу справочников
- регионы;
- должности;
- типы плёнки;
- типы документов;
- типы оборудования;
- RS types.

### 13.3 Можно использовать как основу отчетов
- отчет смены лаборанта;
- отчет смены расшифровщика;
- журналы сменных операций;
- export по сотрудникам, организациям и оборудованию.

## 14. Что нельзя переносить без проверки

### 14.1 Демо-экспортные примеры
- `Реестр заявок РК`.
- `Сотрудники` демо-экспорт.
- демо PDF-акты.
- Эти шаблоны требуют проверки на предмет реальной бизнес-ценности.

### 14.2 Неподтвержденные update-формы
- Любые update requests, которые не были изучены полностью, нельзя переносить как точную спецификацию.

### 14.3 Неявные бизнес-правила из UI
- Если правило видно только в интерфейсе и не подтверждено backend-валидацией, его нельзя считать окончательным.

### 14.4 Неочевидные архивные сценарии
- Для сменных отчетов и журналов архивация не подтверждена как отдельный бизнес-процесс.