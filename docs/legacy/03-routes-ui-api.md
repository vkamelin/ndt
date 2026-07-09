# Маршруты, страницы, API и пользовательские сценарии

## 1. Назначение отчета
Этот отчет фиксирует, какие страницы, маршруты, JSON-endpoint-ы и пользовательские сценарии реально присутствуют в старой версии приложения.

Цель документа:
- восстановить карту пользовательской навигации;
- отделить страницы от служебных и JSON-эндпоинтов;
- показать, где сценарии завершены, а где только начаты;
- зафиксировать расхождения между frontend и backend.

Источники для анализа:
- `routes/web.php`
- `routes/api.php`
- `app/Modules/Core/Http/Controllers/*`
- `app/Modules/Organizations/Controllers/*`
- `app/Modules/Documents/Http/Controllers/*`
- `app/Modules/Equipment/Http/Controllers/*`
- `app/Modules/Shifts/Http/Controllers/*`
- `app/Http/Controllers/FileDownloadController.php`
- `app/Http/Controllers/ExportExampleController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Middleware/CheckUserStatus.php`
- `bootstrap/app.php`
- `resources/js/components/Sidebar.vue`
- `resources/js/components/Can.vue`
- `resources/js/composables/useAuth.ts`
- страницы и модалки в `resources/js/modules/**`
- служебные страницы `resources/js/Pages/Index.vue` и `resources/js/Pages/PageTemplate.vue`

## 2. Изученные источники

### Роуты
- `routes/web.php`
- `routes/api.php`
- `app/Modules/Organizations/routes/web.php`
- `app/Modules/Documents/routes/web.php`
- `app/Modules/Equipment/routes/web.php`

### Контроллеры и middleware
- `app/Modules/Core/Http/Controllers/AuthController.php`
- `app/Modules/Core/Http/Controllers/DashboardController.php`
- `app/Modules/Core/Http/Controllers/UserController.php`
- `app/Modules/Core/Http/Controllers/RoleController.php`
- `app/Modules/Core/Http/Controllers/EmployeeController.php`
- `app/Modules/Core/Http/Controllers/BranchController.php`
- `app/Modules/Core/Http/Controllers/ObjectController.php`
- `app/Modules/Core/Http/Controllers/PositionController.php`
- `app/Modules/Core/Http/Controllers/QueueController.php`
- `app/Modules/Organizations/Controllers/OrganizationController.php`
- `app/Modules/Organizations/Controllers/OrganizationContactController.php`
- `app/Modules/Organizations/Controllers/OrganizationBankAccountController.php`
- `app/Modules/Documents/Http/Controllers/DocumentController.php`
- `app/Modules/Documents/Http/Controllers/DocumentTypeController.php`
- `app/Modules/Equipment/Http/Controllers/*`
- `app/Modules/Shifts/Http/Controllers/*`
- `app/Http/Controllers/FileDownloadController.php`
- `app/Http/Controllers/ExportExampleController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Middleware/CheckUserStatus.php`

### Frontend
- `resources/js/components/Sidebar.vue`
- `resources/js/components/Can.vue`
- `resources/js/composables/useAuth.ts`
- `resources/js/modules/Core/Pages/**`
- `resources/js/modules/Core/Components/**`
- `resources/js/modules/Organizations/Pages/**`
- `resources/js/modules/Organizations/Components/**`
- `resources/js/modules/Documents/Pages/**`
- `resources/js/modules/Documents/Components/**`
- `resources/js/modules/Equipment/Pages/**`
- `resources/js/modules/Equipment/Components/**`
- `resources/js/modules/Shifts/Pages/**`
- `resources/js/modules/Shifts/Components/**`
- `resources/js/Pages/Index.vue`
- `resources/js/Pages/PageTemplate.vue`

## 3. Общая карта маршрутов

### 3.1 Публичные и auth-маршруты
| Метод | URL | Назначение | Контроллер | Тип |
|---|---|---|---|---|
| GET | `/login` | Форма входа | `AuthController@showLoginForm` | Страница |
| POST | `/login` | Авторизация | `AuthController@login` | Действие формы |
| GET | `/logout` | Выход | `AuthController@logout` | Действие |
| GET | `/files/{token}` | Скачивание/просмотр файла по signed token | `FileDownloadController@show` | Служебный download |
| GET | `/api/user` | Текущий user для Sanctum | `routes/api.php` closure | API |

### 3.2 Общие authenticated-маршруты
| Метод | URL | Назначение | Контроллер | Тип |
|---|---|---|---|---|
| GET | `/` | Dashboard | `DashboardController@index` | Страница |
| GET | `/profile` | Тот же dashboard, как экран профиля | `DashboardController@index` | Страница |
| POST | `/profile/change-password` | Смена пароля | `DashboardController@changePassword` | Действие формы |
| GET | `/profile/notification-settings` | Настройки уведомлений | `DashboardController@notificationSettings` | JSON |
| PUT | `/profile/notification-settings` | Сохранение настроек уведомлений | `DashboardController@updateNotificationSettings` | Действие формы |
| GET | `/profile/medical-examinations` | Личный журнал медосмотров | `DashboardController@medicalExaminations` | Страница |
| GET | `/profile/briefings` | Личный журнал инструктажей | `DashboardController@briefings` | Страница |
| GET | `/profile/ppe-items` | Личный журнал СИЗ | `DashboardController@ppeItems` | Страница |
| GET | `/my-profile` | Карточка сотрудника пользователя | `EmployeeController@myProfile` | Страница |

### 3.3 Core: пользователи, роли, сотрудники, филиалы, объекты, должности, очереди
| Prefix | Основные методы | Контроллеры | Тип |
|---|---|---|---|
| `/users` | GET, POST, GET `/edit`, PUT, DELETE | `UserController` | Страница + JSON edit |
| `/roles` | GET, POST, GET `/edit`, PUT | `RoleController` | Страница + JSON edit |
| `/employees` | GET, GET `/create`, POST, GET `/{employee}`, GET `/{employee}/edit`, PUT, DELETE, GET `/export`, GET `/{employee}/print` | `EmployeeController`, `EmployeeExportController` | Страницы + export + upload |
| `/branches` | GET, POST, GET `/{branch}`, GET `/{branch}/edit`, PUT, DELETE | `BranchController` | Страница + JSON view/edit |
| `/objects` | GET, GET `/create`, POST, GET `/{object}`, GET `/{object}/edit`, PUT, DELETE | `ObjectController` | Страницы |
| `/positions` | GET, POST, GET `/{position}/edit`, PUT, DELETE, POST `/{position}/toggle` | `PositionController` | Страница + JSON edit + action |
| `/queues` | GET, POST retry, DELETE failed jobs | `QueueController` | Страница + actions |
| `/exports/examples` | GET examples | `ExportExampleController` | Demo/export |

### 3.4 Organizations
| Метод | URL | Назначение | Контроллер | Тип |
|---|---|---|---|---|
| GET | `/organizations` | Список организаций | `OrganizationController@index` | Страница |
| GET | `/organizations/export` | Excel export | `OrganizationsExportController` | Download |
| POST | `/organizations` | Создание | `OrganizationController@store` | Действие формы |
| GET | `/organizations/{organization}` | Карточка организации | `OrganizationController@show` | Страница |
| GET | `/organizations/{organization}/print` | PDF печать карточки | `OrganizationController@print` | Download |
| GET | `/organizations/{organization}/edit` | Данные для модалки | `OrganizationController@edit` | JSON |
| PUT | `/organizations/{organization}` | Обновление | `OrganizationController@update` | Действие формы |
| DELETE | `/organizations/{organization}` | Удаление | `OrganizationController@destroy` | Действие |
| POST | `/organizations/{organization}/restore` | Восстановление soft delete | `OrganizationController@restore` | Действие |
| POST | `/organizations/{organization}/contacts` | Добавить контакт | `OrganizationContactController@store` | Действие формы |
| PUT | `/organizations/contacts/{contact}` | Обновить контакт | `OrganizationContactController@update` | Действие формы |
| DELETE | `/organizations/contacts/{contact}` | Удалить контакт | `OrganizationContactController@destroy` | Действие |
| POST | `/organizations/{organization}/bank-accounts` | Добавить реквизиты | `OrganizationBankAccountController@store` | Действие формы |
| PUT | `/organizations/bank-accounts/{bankAccount}` | Обновить реквизиты | `OrganizationBankAccountController@update` | Действие формы |
| DELETE | `/organizations/bank-accounts/{bankAccount}` | Удалить реквизиты | `OrganizationBankAccountController@destroy` | Действие |

### 3.5 Documents
| Prefix | Основные методы | Контроллеры | Тип |
|---|---|---|---|
| `/documents/document-types` | GET, POST, GET `/edit`, PUT, DELETE | `DocumentTypeController` | Страница + JSON edit |
| `/documents` | GET, GET `/create`, POST, GET `/{document}`, GET `/{document}/edit`, PUT, DELETE | `DocumentController` | Страницы |
| `/documents/{document}/files` | POST, DELETE | `DocumentController` | Связанные действия |
| `/documents/{document}/relations` | POST, DELETE | `DocumentController` | Связанные действия |
| `/documents/{document}/versions` | POST | `DocumentController` | Связанное действие |

### 3.6 Equipment
| Prefix | Основные методы | Контроллеры | Тип |
|---|---|---|---|
| `/equipment` | GET, GET `/export`, POST, GET `/{equipment}`, GET `/{equipment}/edit`, PUT, DELETE | `EquipmentController`, `EquipmentExportController` | Страницы + JSON edit + export |
| `/equipment/equipment-types` | GET, POST, GET `/edit`, PUT, DELETE | `EquipmentTypeController` | Страница + JSON edit |
| `/equipment/equipment-calibrations` | GET, POST, POST `/documents/upload`, GET `/edit`, PUT, DELETE | `EquipmentCalibrationController` | Страница + upload + JSON edit |
| `/equipment/equipment-verifications` | GET, POST, POST `/documents/upload`, GET `/edit`, PUT, DELETE | `EquipmentVerificationController` | Страница + upload + JSON edit |
| `/equipment/equipment-maintenances` | GET, POST, POST `/documents/upload`, GET `/edit`, PUT, DELETE | `EquipmentMaintenanceController` | Страница + upload + JSON edit |
| `/equipment/equipment-assignments` | GET, POST, POST `/documents/upload`, GET `/edit`, PUT, DELETE | `EquipmentAssignmentController` | Страница + upload + JSON edit |
| `/equipment/equipment-documents` | GET, POST, POST `/documents/upload`, GET `/edit`, PUT, DELETE | `EquipmentDocumentController` | Страница + upload + JSON edit |
| `/equipment/equipment-defects` | GET, POST, POST `/documents/upload`, GET `/edit`, PUT, DELETE | `EquipmentDefectController` | Страница + upload + JSON edit |
| `/equipment/equipment-movements` | GET, POST, POST `/documents/upload`, GET `/edit`, PUT, DELETE | `EquipmentMovementController` | Страница + upload + JSON edit |

### 3.7 Shifts, journals, reports
| Метод | URL | Назначение | Контроллер | Тип |
|---|---|---|---|---|
| POST | `/shifts/laborant/start` | Старт смены | `ShiftController@start` | Action |
| POST | `/shifts/laborant/finish` | Завершение смены | `ShiftController@finish` | Action |
| POST | `/shifts/reset` | Сброс сменных данных (dev) | `ShiftController@reset` | Action |
| GET | `/shifts/laborant/maintenance/state` | Состояние регламентных работ | `ShiftController@maintenanceState` | JSON |
| POST | `/shifts/laborant/maintenance/save` | Сохранение регламента | `ShiftController@saveMaintenance` | Action |
| POST | `/shifts/laborant/film-receipts` | Поступление плёнки | `LaborantShiftController@storeFilmReceipt` | Action |
| POST | `/shifts/laborant/chemical-receipts` | Поступление химии | `LaborantShiftController@storeChemicalReceipt` | Action |
| POST | `/shifts/laborant/film-requests` | Запрос плёнки | `LaborantShiftController@storeFilmRequest` | Action |
| POST | `/shifts/laborant/chemical-requests` | Запрос химии | `LaborantShiftController@storeChemicalRequest` | Action |
| POST | `/shifts/laborant/chemical-replacements` | Замена химии | `LaborantShiftController@storeChemicalReplacement` | Action |
| POST | `/shifts/laborant/film-issues` | Выдача плёнки | `LaborantShiftController@storeFilmIssue` | Action |
| POST | `/shifts/decoder/film-groups` | Группа просмотренной плёнки | `DecoderShiftController@storeFilmGroup` | Action |
| POST | `/shifts/decoder/rejects` | Запись брака | `DecoderShiftController@storeReject` | Action |
| POST | `/shifts/decoder/forgery` | Подозрение на подлог | `DecoderShiftController@saveForgery` | Action |
| POST | `/shifts/decoder/cleanup` | Уборка рабочего места | `DecoderShiftController@saveCleanup` | Action |
| POST | `/shifts/decoder/decryptions` | Запись дешифровки | `DecoderShiftController@storeDecryption` | Action |
| GET | `/film-inventory-transactions` | Журнал плёнки | `FilmInventoryTransactionController@index` | Страница |
| GET | `/film-inventory-transactions/export` | Excel export журнала | `FilmInventoryTransactionController@export` | Download |
| GET | `/developing-chemical-transactions` | Журнал химии | `DevelopingChemicalTransactionController@index` | Страница |
| GET | `/developing-chemical-transactions/export` | Excel export журнала | `DevelopingChemicalTransactionController@export` | Download |
| GET | `/chemical-requests` | Журнал запросов химии | `ChemicalRequestController@index` | Страница |
| GET | `/chemical-requests/export` | Excel export журнала | `ChemicalRequestController@export` | Download |
| POST | `/chemical-requests/{chemicalRequest}/complete` | Закрыть запрос как выполненный | `ChemicalRequestController@complete` | Action |
| GET | `/my-reports` | Мои отчеты | `MyReportController@index` | Страница |
| GET | `/lab-shift-reports` | Отчеты лаборантов | `LabShiftReportController@index` | Страница |
| GET | `/lab-shift-reports/{id}` | Карточка отчета лаборанта | `LabShiftReportController@show` | Страница |
| GET | `/decoder-shift-reports` | Отчеты расшифровщиков | `DecoderShiftReportController@index` | Страница |
| GET | `/decoder-shift-reports/{id}` | Карточка отчета расшифровщика | `DecoderShiftReportController@show` | Страница |
| GET | `/decoder-shift-film-groups` | Журнал групп просмотренной плёнки | `DecoderShiftFilmGroupController@index` | Страница |
| GET | `/decoder-shift-film-groups/export` | Excel export | `DecoderShiftFilmGroupController@export` | Download |
| GET | `/decoder-shift-film-groups/{decoderShiftFilmGroup}` | Карточка записи | `DecoderShiftFilmGroupController@show` | Страница |
| GET | `/decoder-shift-rejects` | Журнал брака | `DecoderShiftRejectController@index` | Страница |
| GET | `/decoder-shift-rejects/export` | Excel export | `DecoderShiftRejectController@export` | Download |
| GET | `/decoder-shift-rejects/{decoderShiftReject}` | Карточка записи | `DecoderShiftRejectController@show` | Страница |
| GET | `/decoder-shift-forgery-suspicions` | Журнал подлога | `DecoderShiftForgerySuspicionController@index` | Страница |
| GET | `/decoder-shift-forgery-suspicions/export` | Excel export | `DecoderShiftForgerySuspicionController@export` | Download |
| GET | `/decoder-shift-forgery-suspicions/{decoderShiftForgerySuspicion}` | Карточка записи | `DecoderShiftForgerySuspicionController@show` | Страница |
| GET | `/decoder-shift-decryptions` | Журнал дешифровки | `DecoderShiftDecryptionJournalController@index` | Страница |
| GET | `/decoder-shift-decryptions/export` | Excel export | `DecoderShiftDecryptionJournalController@export` | Download |

## 4. Страницы приложения

### 4.1 Базовые страницы
- `Core::Auth/Login` - форма входа.
  - Поля: email, пароль, remember.
  - Действие: `POST /login`.
  - Источник: `AuthController@showLoginForm`, `resources/js/modules/Core/Pages/Auth/Login.vue`.
- `Core::Dashboard` - главная панель.
  - Показывает приветствие, объект, дату/время, быстрые действия, состояние смены, уведомления, ближайшие медосмотры/инструктажи/СИЗ.
  - Источник: `DashboardController@index`, `DashboardService@getDashboardData`, `resources/js/modules/Core/Pages/Dashboard.vue`.
- `Core::Employees/Show` как `my-profile`.
  - Использует тот же компонент, что и карточка сотрудника, но с флагом `isSelfProfile = true`.
  - Источник: `EmployeeController@myProfile`, `EmployeeController@show`.

### 4.2 Профиль пользователя
- `Core::MedicalExaminations/Index`
  - Личный журнал медосмотров с фильтрами по статусу и датам.
  - Источник: `DashboardController@medicalExaminations`, `resources/js/modules/Core/Pages/MedicalExaminations/Index.vue`.
- `Core::Briefings/Index`
  - Личный журнал инструктажей.
  - Источник: `DashboardController@briefings`, `resources/js/modules/Core/Pages/Briefings/Index.vue`.
- `Core::PpeItems/Index`
  - Личный журнал СИЗ.
  - Источник: `DashboardController@ppeItems`, `resources/js/modules/Core/Pages/PpeItems/Index.vue`.

### 4.3 Карточки и списки core-сущностей
- `Core::Users/Index`
  - Таблица пользователей, фильтр по имени/email/ролям/статусу, сортировка, создание и редактирование через модалки.
  - Источник: `UserController@index`, `UserController@store`, `UserController@edit`, `resources/js/modules/Core/Pages/Users/Index.vue`.
- `Core::Roles/Index`
  - Таблица ролей, создание и редактирование через модалки, выбор permissions.
  - Источник: `RoleController@index`, `RoleController@store`, `RoleController@edit`, `resources/js/modules/Core/Pages/Roles/Index.vue`.
- `Core::Employees/Index`
  - Реестр сотрудников, фильтр по статусу и поиску, экспорт в Excel, создание/редактирование/удаление.
  - Источник: `EmployeeController@index`, `EmployeeExportController`, `resources/js/modules/Core/Pages/Employees/Index.vue`.
- `Core::Employees/Create` и `Core::Employees/Edit`
  - Большая форма сотрудника с вкладками: персональные данные, HR, антропометрия, квалификации, медосмотры, инструктажи, СИЗ, обучение, документы.
  - Источник: `EmployeeController@create`, `EmployeeController@edit`, `resources/js/modules/Core/Components/Employees/EmployeeForm.vue`.
- `Core::Employees/Show`
  - Карточка сотрудника с теми же вкладками и печатью PDF.
  - Источник: `EmployeeController@show`, `EmployeeController@print`, `resources/js/modules/Core/Pages/Employees/Show.vue`.
- `Core::Branches/Index`
  - Таблица филиалов, фильтры, создание/редактирование через модалки, удаление.
  - Источник: `BranchController@index`, `BranchController@edit`, `resources/js/modules/Core/Pages/Branches/Index.vue`.
- `Core::Objects/Index`
  - Реестр объектов с фильтрацией, сортировкой, CRUD.
  - Источник: `ObjectController@index`, `resources/js/modules/Core/Pages/Objects/Index.vue`.
- `Core::Objects/Create`, `Core::Objects/Edit`, `Core::Objects/Show`
  - Форма и карточка объекта, вкладки "Общие" и "Оборудование".
  - Источник: `ObjectController@create`, `ObjectController@edit`, `ObjectController@show`, `resources/js/modules/Core/Components/Objects/ObjectForm.vue`, `resources/js/modules/Core/Pages/Objects/Show.vue`.
- `Core::Positions/Index`
  - Справочник должностей, модалка create/edit, toggle активности, удаление.
  - Источник: `PositionController@index`, `PositionController@toggle`, `resources/js/modules/Core/Pages/Positions/Index.vue`.
- `Core::Queues/Index`
  - Мониторинг очередей Laravel и доставок уведомлений.
  - Источник: `QueueController@index`, `resources/js/modules/Core/Pages/Queues/Index.vue`.

### 4.4 Organizations
- `Organizations::Organizations/Index`
  - Реестр организаций с фильтрами по региону, НДС, наличию контактов и банковских счетов.
  - Источник: `OrganizationController@index`, `resources/js/modules/Organizations/Pages/Organizations/Index.vue`.
- `Organizations::Organizations/Show`
  - Карточка организации с вкладками "Основные данные", "Контакты", "Банковские реквизиты", "История изменений".
  - Источник: `OrganizationController@show`, `resources/js/modules/Organizations/Pages/Organizations/Show.vue`.
- `Organizations::Organizations/Edit` как JSON для модалки.
  - Фронтенд открывает модалку и загружает данные через `GET /organizations/{id}/edit`.
  - Источник: `OrganizationController@edit`, `resources/js/modules/Organizations/Pages/Organizations/Index.vue`, `resources/js/modules/Organizations/Components/Organizations/OrganizationEditModal.vue`.

### 4.5 Documents
- `Documents::DocumentTypes/Index`
  - Справочник типов документов, создание/редактирование через модалки.
  - Источник: `DocumentTypeController@index`, `DocumentTypeController@edit`, `resources/js/modules/Documents/Pages/DocumentTypes/Index.vue`.
- `Documents::Documents/Index`
  - Реестр документов с поиском, фильтрами и сортировкой.
  - Источник: `DocumentController@index`, `resources/js/modules/Documents/Pages/Documents/Index.vue`.
- `Documents::Documents/Create`, `Documents::Documents/Edit`
  - Форма документа с вкладками "Основные данные", "Реквизиты", "Привязка", "Сроки и контроль", "Связанные данные".
  - Источник: `DocumentController@create`, `DocumentController@edit`, `resources/js/modules/Documents/Components/Documents/DocumentForm.vue`.
- `Documents::Documents/Show`
  - Карточка документа с вкладками, связями, файлами, версиями и тегами.
  - Источник: `DocumentController@show`, `resources/js/modules/Documents/Pages/Documents/Show.vue`.

### 4.6 Equipment
- `Equipment::Equipment/Index`
  - Реестр оборудования с большим числом фильтров и модалками create/edit/export.
  - Источник: `EquipmentController@index`, `resources/js/modules/Equipment/Pages/Equipment/Index.vue`.
- `Equipment::Equipment/Show`
  - Карточка оборудования.
  - Источник: `EquipmentController@show`, `resources/js/modules/Equipment/Pages/Equipment/Show.vue`.
- `Equipment::EquipmentTypes/Index`
  - Справочник типов оборудования.
  - Источник: `EquipmentTypeController@index`, `resources/js/modules/Equipment/Pages/EquipmentTypes/Index.vue`.
- `Equipment::EquipmentVerifications/Index`
  - Журнал поверок с create/edit модалками и upload сканов.
  - Источник: `EquipmentVerificationController@index`, `resources/js/modules/Equipment/Pages/EquipmentVerifications/Index.vue`.
- `Equipment::EquipmentCalibrations/Index`
  - Журнал калибровок.
  - Источник: `EquipmentCalibrationController@index`, `resources/js/modules/Equipment/Pages/EquipmentCalibrations/Index.vue`.
- `Equipment::EquipmentMaintenances/Index`
  - Журнал ТО/ремонтов.
  - Источник: `EquipmentMaintenanceController@index`, `resources/js/modules/Equipment/Pages/EquipmentMaintenances/Index.vue`.
- `Equipment::EquipmentAssignments/Index`
  - Журнал выдачи/назначения оборудования.
  - Источник: `EquipmentAssignmentController@index`, `resources/js/modules/Equipment/Pages/EquipmentAssignments/Index.vue`.
- `Equipment::EquipmentDocuments/Index`
  - Реестр документов оборудования.
  - Источник: `EquipmentDocumentController@index`, `resources/js/modules/Equipment/Pages/EquipmentDocuments/Index.vue`.
- `Equipment::EquipmentDefects/Index`
  - Журнал дефектов.
  - Источник: `EquipmentDefectController@index`, `resources/js/modules/Equipment/Pages/EquipmentDefects/Index.vue`.
- `Equipment::EquipmentMovements/Index`
  - Журнал перемещений оборудования.
  - Источник: `EquipmentMovementController@index`, `resources/js/modules/Equipment/Pages/EquipmentMovements/Index.vue`.

### 4.7 Shifts and journals
- `Shifts::MyReports/Index`
  - Личная страница отчетов, на которой пользователь видит либо лаборантские, либо decoder-отчеты, либо пустое состояние.
  - Источник: `MyReportController@index`, `resources/js/modules/Shifts/Pages/MyReports/Index.vue`.
- `Shifts::LabShiftReports/Index` и `Show`
  - Отчеты смен лаборантов и детальная карточка смены.
  - Источник: `LabShiftReportController@index`, `LabShiftReportController@show`, `resources/js/modules/Shifts/Pages/LabShiftReports/*`.
- `Shifts::DecoderShiftReports/Index` и `Show`
  - Отчеты смен расшифровщиков и детальная карточка смены с журналом дешифровок.
  - Источник: `DecoderShiftReportController@index`, `DecoderShiftReportController@show`, `resources/js/modules/Shifts/Pages/DecoderShiftReports/*`.
- `Shifts::FilmInventoryTransactions/Index`
  - Журнал плёнки.
  - Источник: `FilmInventoryTransactionController@index`, `resources/js/modules/Shifts/Pages/FilmInventoryTransactions/Index.vue`.
- `Shifts::DevelopingChemicalTransactions/Index`
  - Журнал химии.
  - Источник: `DevelopingChemicalTransactionController@index`, `resources/js/modules/Shifts/Pages/DevelopingChemicalTransactions/Index.vue`.
- `Shifts::ChemicalRequests/Index`
  - Журнал запросов химии, закрытие запроса через модалку.
  - Источник: `ChemicalRequestController@index`, `resources/js/modules/Shifts/Pages/ChemicalRequests/Index.vue`.
- `Shifts::DecoderShiftFilmGroups/Index` и `Show`
  - Журнал просмотренной плёнки.
  - Источник: `DecoderShiftFilmGroupController@index`, `DecoderShiftFilmGroupController@show`, `resources/js/modules/Shifts/Pages/DecoderShiftFilmGroups/*`.
- `Shifts::DecoderShiftRejects/Index` и `Show`
  - Журнал брака.
  - Источник: `DecoderShiftRejectController@index`, `DecoderShiftRejectController@show`, `resources/js/modules/Shifts/Pages/DecoderShiftRejects/*`.
- `Shifts::DecoderShiftForgerySuspicions/Index` и `Show`
  - Журнал подлогов.
  - Источник: `DecoderShiftForgerySuspicionController@index`, `DecoderShiftForgerySuspicionController@show`, `resources/js/modules/Shifts/Pages/DecoderShiftForgerySuspicions/*`.
- `Shifts::DecoderShiftDecryptions/Index`
  - Журнал дешифровок.
  - Источник: `DecoderShiftDecryptionJournalController@index`, `resources/js/modules/Shifts/Pages/DecoderShiftDecryptions/Index.vue`.

## 5. Административные страницы

### Справочники и системные разделы
- `Users`
  - Управление пользователями, ролями, статусом, удалением.
  - Источник: `UserController@index/store/edit/update/destroy`, `resources/js/modules/Core/Pages/Users/Index.vue`.
- `Roles`
  - Управление ролями и их permissions.
  - Источник: `RoleController@index/store/edit/update`, `resources/js/modules/Core/Pages/Roles/Index.vue`.
- `Positions`
  - Справочник должностей с привязкой к роли доступа и переключением активности.
  - Источник: `PositionController`, `resources/js/modules/Core/Pages/Positions/Index.vue`, `PositionFormModal.vue`.
- `Branches`
  - Справочник филиалов.
  - Источник: `BranchController`, `resources/js/modules/Core/Pages/Branches/Index.vue`.
- `Document types`
  - Справочник типов документов.
  - Источник: `DocumentTypeController`, `resources/js/modules/Documents/Pages/DocumentTypes/Index.vue`.
- `Equipment types`
  - Справочник типов оборудования.
  - Источник: `EquipmentTypeController`, `resources/js/modules/Equipment/Pages/EquipmentTypes/Index.vue`.

### Операционные журналы
- `Queues`
  - Админский мониторинг очередей и уведомлений.
  - Источник: `QueueController`, `resources/js/modules/Core/Pages/Queues/Index.vue`.
- `LabShiftReports` и `DecoderShiftReports`
  - Доступны только при соответствующих permissions; в сайдбаре видны через роль `admin`.
  - Источник: `Sidebar.vue`, `LabShiftReportController`, `DecoderShiftReportController`.
- `FilmInventoryTransactions`, `DevelopingChemicalTransactions`, `ChemicalRequests`, `DecoderShiftFilmGroups`, `DecoderShiftRejects`, `DecoderShiftForgerySuspicions`, `DecoderShiftDecryptions`
  - Журналы производства и контроля.
  - Источник: `routes/web.php`, `Sidebar.vue`, соответствующие контроллеры.

### Условно административные, но не в главном меню
- `EquipmentVerifications`, `EquipmentCalibrations`, `EquipmentMaintenances`, `EquipmentAssignments`, `EquipmentDocuments`, `EquipmentDefects`, `EquipmentMovements`
  - В интерфейсе это отдельные рабочие журналы, а не отдельные пункты меню.
  - Источник: `app/Modules/Equipment/routes/web.php`, страницы `resources/js/modules/Equipment/Pages/*`.

## 6. API

### 6.1 Формальный API
| Метод | URL | Назначение | Вход | Ответ | Доступ | Frontend | Статус |
|---|---|---|---|---|---|---|---|
| GET | `/api/user` | Получить текущего пользователя Sanctum | Auth session | JSON `User` | `auth:sanctum` | В текущем frontend прямого использования не найдено | Завершен |

### 6.2 JSON-endpoint-ы, используемые как UI API
| Метод | URL | Назначение | Вход | Ответ | Доступ | Frontend | Статус |
|---|---|---|---|---|---|---|---|
| GET | `/users/{user}/edit` | Данные для модалки редактирования пользователя | route-model `user` | JSON `{ user, roles, user_role_ids }` | `authorize update` | Да, `Users/Index.vue` | Завершен |
| GET | `/roles/{role}/edit` | Данные для модалки редактирования роли | route-model `role` | JSON `{ role, permissions, role_permission_names }` | `authorize update` | Да, `Roles/Index.vue` | Завершен |
| GET | `/branches/{branch}/edit` | Данные для модалки филиала | route-model `branch` | JSON `{ branch }` | `authorize update` | Да, `Branches/Index.vue` | Завершен |
| GET | `/branches/{branch}` | Карточка филиала в JSON | route-model `branch` | JSON `{ branch }` | `authorize view` | UI route есть, но это не отдельная страница | Завершен |
| GET | `/organizations/{organization}/edit` | Данные для модалки организации | route-model `organization` | JSON `{ organization }` | `authorize update` | Да, `Organizations/Index.vue` | Завершен |
| GET | `/equipment/{equipment}/edit` | Данные для модалки оборудования | route-model `equipment` | JSON `{ equipment }` | `authorize update` | Да, `Equipment/Index.vue` | Завершен |
| GET | `/equipment/equipment-types/{id}/edit` | Данные для модалки типа оборудования | route-model id | JSON `{ equipmentType }` | `authorize update` | Да | Завершен |
| GET | `/equipment/equipment-verifications/{id}/edit` | Данные для модалки поверки | route-model id | JSON `{ equipmentVerification }` | `authorize update` | Да | Завершен |
| GET | `/equipment/equipment-calibrations/{id}/edit` | Данные для модалки калибровки | route-model id | JSON `{ equipmentCalibration }` | `authorize update` | Да | Завершен |
| GET | `/equipment/equipment-maintenances/{id}/edit` | Данные для модалки ТО | route-model id | JSON `{ equipmentMaintenance }` | `authorize update` | Да | Завершен |
| GET | `/equipment/equipment-assignments/{id}/edit` | Данные для модалки выдачи | route-model id | JSON `{ equipmentAssignment }` | `authorize update` | Да | Завершен |
| GET | `/equipment/equipment-documents/{id}/edit` | Данные для модалки документа оборудования | route-model id | JSON `{ equipmentDocument }` | `authorize update` | Да | Завершен |
| GET | `/equipment/equipment-defects/{id}/edit` | Данные для модалки дефекта | route-model id | JSON `{ equipmentDefect }` | `authorize update` | Да | Завершен |
| GET | `/equipment/equipment-movements/{id}/edit` | Данные для модалки перемещения | route-model id | JSON `{ equipmentMovement }` | `authorize update` | Да | Завершен |
| GET | `/profile/notification-settings` | Показать настройки уведомлений | текущий user | JSON `{ settings }` | auth | Да, modal | Завершен |
| GET | `/shifts/laborant/maintenance/state` | Состояние регламентных работ открытой смены | текущий user | JSON `{ state }` или `422` | `authorize finish` | Да, dashboard modal | Завершен |
| POST | `/employees/documents/upload` | Загрузка файла документа сотрудника | `file` | JSON `{ path, url }` | `authorize create` | Да, `EmployeeDocumentModal` | Завершен |
| POST | `/employees/medical-examinations/upload` | Загрузка скана медосмотра | `scan` | JSON `{ path, url }` | `authorize create` | Да, `EmployeeMedicalExaminationModal` | Завершен |
| POST | `/equipment/*/documents/upload` | Загрузка файла для оборудования | `file` | JSON `{ path, url }` | `authorize create` | Да, equipment modals | Завершен |

### 6.3 Возможные ошибки endpoint-ов
- `403` - нет permission или policy не пропускает.
- `404` - модель не найдена.
- `422` - validation error.
- Для `FileDownloadController@show` дополнительно:
  - `410` - истекла ссылка;
  - `403` - подпись URL недействительна;
  - `404` - файл не восстановлен из token.

Источники:
- `routes/web.php`
- `app/Modules/*/Http/Controllers/*`
- `resources/js/modules/**`
- `app/Services/FileStorageService.php`

## 7. Формы и действия пользователей

### Core
- Вход/выход: логин, logout.
  - Источник: `AuthController`, `Login.vue`.
- Изменение пароля и уведомлений на dashboard.
  - Источник: `DashboardController`, `ChangePasswordModal.vue`, `NotificationSettingsModal.vue`.
- Пользователи:
  - создание, редактирование, soft delete;
  - редактирование ролей пользователя;
  - источник: `Users/Index.vue`, `UserCreateModal.vue`, `UserEditModal.vue`.
- Роли:
  - создание, редактирование, назначение/снятие permissions;
  - источник: `Roles/Index.vue`, `RoleCreateModal.vue`, `RoleEditModal.vue`, `RolePermissionsSelector.vue`.
- Сотрудники:
  - создание и редактирование большой формы;
  - печать PDF карточки;
  - загрузка файла документа и скана медосмотра;
  - источник: `EmployeeForm.vue`, `EmployeeDocumentModal.vue`, `EmployeeMedicalExaminationModal.vue`, `EmployeeController@print`.
- Должности:
  - создание/редактирование через modal;
  - toggle активности;
  - удаление;
  - источник: `PositionFormModal.vue`, `PositionController@toggle`.
- Филиалы:
  - создание/редактирование/удаление;
  - просмотр в JSON для модалки;
  - источник: `Branches/Index.vue`, `BranchEditModal.vue`.
- Объекты:
  - create/edit/show;
  - связь с филиалом, эксплуатирующей организацией, ответственным сотрудником, заказчиками, оборудованием;
  - источник: `ObjectForm.vue`, `Objects/Show.vue`.

### Organizations
- Создание организации через модалку.
- Редактирование через модалку с отдельными формами контактов и банковских счетов.
- Просмотр карточки с вкладками и историей изменений.
- Печать PDF.
- Источник: `OrganizationCreateModal.vue`, `OrganizationEditModal.vue`, `Organizations/Show.vue`.

### Documents
- Создание и редактирование документа.
- Ввод реквизитов, привязок, тегов, файлов, связей и версий.
- Просмотр карточки со сводкой по файлам/связям/версиям.
- Источник: `DocumentForm.vue`, `Documents/Create.vue`, `Documents/Edit.vue`, `Documents/Show.vue`.

### Equipment
- Создание и редактирование оборудования.
- Экспорт в Excel.
- Управление типами оборудования.
- Журналы поверок, калибровок, ТО, выдачи, документов, дефектов, перемещений.
- Загрузка файлов в журналах.
- Источник: `Equipment/Index.vue`, `EquipmentCreateModal.vue`, `EquipmentEditModal.vue`, `EquipmentExportModal.vue`, и модалки журналов.

### Shifts / production workflow
- Старт и завершение смены.
- Регламентные работы в смене.
- Поступление плёнки и химии.
- Запросы плёнки и химии.
- Выдача плёнки.
- Замена химии.
- Для decoder:
  - группы просмотренной плёнки;
  - записи брака;
  - подозрения на подлог;
  - уборка;
  - дешифровка.
- Источник: `Dashboard.vue`, `ShiftController`, `LaborantShiftController`, `DecoderShiftController`, modal-компоненты в `resources/js/modules/Shifts/Components/Shifts/*`.

### Reports and journals
- Фильтрация, сортировка и pagination в журналах.
- Экспорт в Excel для журналов плёнки, химии, decoder-записей.
- Drill-down от отчета к журналам.
- Источник: `LabShiftReportController`, `DecoderShiftReportController`, `MyReport.vue`, журнальные страницы.

## 8. Пользовательские сценарии

### Сценарий 1. Вход в систему
- Пользователь открывает `/login`, вводит email и пароль, может включить remember.
- После успеха попадает на dashboard.
- Источник: `AuthController`, `Login.vue`.

### Сценарий 2. Работа с личным профилем
- Пользователь открывает dashboard или `/profile`.
- Может:
  - изменить пароль;
  - открыть настройки уведомлений;
  - посмотреть личные журналы медосмотров, инструктажей и СИЗ;
  - перейти в `my-profile`.
- Источник: `DashboardController`, `Dashboard.vue`, `Sidebar.vue`.

### Сценарий 3. Управление сотрудниками
- Создание сотрудника.
- Редактирование сотрудника.
- Просмотр карточки сотрудника.
- Печать карточки.
- Загрузка документов и медсканов.
- Частично видны связанные сущности:
  - квалификации;
  - медосмотры;
  - инструктажи;
  - СИЗ;
  - обучение;
  - документы.
- Источник: `EmployeeController`, `EmployeeForm.vue`, `Employees/Show.vue`.

### Сценарий 4. Управление организацией
- Пользователь открывает список организаций.
- Создает новую организацию.
- Редактирует реквизиты, контакты и банковские счета через модалку.
- Может удалить и восстановить организацию.
- Может распечатать карточку в PDF.
- Источник: `OrganizationController`, `Organizations/Index.vue`, `OrganizationEditModal.vue`, `Organizations/Show.vue`.

### Сценарий 5. Управление объектами
- Пользователь создает объект.
- Заполняет филиал, адрес, даты, эксплуатирующую организацию, ответственного, заказчиков и оборудование.
- Просматривает карточку объекта и связанные оборудования.
- Источник: `ObjectController`, `ObjectForm.vue`, `Objects/Show.vue`.

### Сценарий 6. Управление документами
- Пользователь создает или редактирует документ.
- Может:
  - выбрать тип документа;
  - заполнить реквизиты;
  - привязать владельца;
  - прикрепить теги;
  - загрузить несколько файлов;
  - задать связи с другими документами;
  - вести версии.
- Источник: `DocumentController`, `DocumentForm.vue`, `Documents/Show.vue`.
- Важное замечание: дополнительные backend-роуты `addFile/addRelation/addVersion` существуют, но явного прямого frontend-вызова к ним не найдено. Часть логики может быть сохранена в основной форме, часть выглядит как остаток расширения. Источник: `routes/web.php`, `DocumentController`, `DocumentForm.vue`.

### Сценарий 7. Управление оборудованием
- Пользователь ведет реестр оборудования.
- Может создавать, редактировать, удалять карточки, фильтровать по статусу, состоянию, проверкам, калибровкам, филиалу и ответственному.
- Может экспортировать реестр.
- Источник: `EquipmentController`, `Equipment/Index.vue`, `Equipment/Show.vue`.

### Сценарий 8. Журналы оборудования
- Пользователь создает и редактирует:
  - поверки;
  - калибровки;
  - ТО/ремонты;
  - выдачи;
  - документы оборудования;
  - дефекты;
  - перемещения.
- Во многих журналах есть загрузка файла/скана через отдельный upload-endpoint.
- Источник: контроллеры `Equipment*Controller`, модалки `Equipment*Modal.vue`.

### Сценарий 9. Сменные сценарии лаборанта
- Начать смену.
- Зафиксировать поступление плёнки и химии.
- Отправить запрос на плёнку или химию.
- Зафиксировать замену химии.
- Зафиксировать выдачу плёнки.
- Завершить смену и получить summary.
- Источник: `ShiftController`, `LaborantShiftController`, `Dashboard.vue`.

### Сценарий 10. Сменные сценарии расшифровщика
- Отправить группу просмотренной плёнки.
- Добавить запись брака.
- Зафиксировать подозрение на подлог.
- Заполнить уборку.
- Зафиксировать дешифровку.
- Просмотреть отчет смены и журналы.
- Источник: `DecoderShiftController`, `DecoderShiftReportController`, `Dashboard.vue`.

### Сценарий 11. Отчеты и журналы
- Пользователь открывает личные отчеты и, если есть доступ, общие отчеты.
- Может открыть детальный отчет смены, провалиться в связанные журналы.
- Источник: `MyReportController`, `LabShiftReportController`, `DecoderShiftReportController`.

### Сценарий 12. Очереди и уведомления
- Администратор мониторит очереди Laravel.
- Видит pending/failed jobs и доставки уведомлений.
- Может повторить отправку доставки или failed job.
- Источник: `QueueController`, `Queues/Index.vue`.

## 9. Поиск, фильтры и списки

### Общий паттерн
- Большинство списков реализованы как Inertia-страницы с фильтрами в query string.
- Текстовые поля обычно используют debounce.
- Списки почти всегда имеют:
  - пагинацию;
  - сортировку;
  - кнопки create/edit/delete;
  - status badges.
- Источник: `resources/js/modules/*/Pages/*`, `use*Filters.ts`, `useDebounce.ts`.

### Типовые фильтры
- Пользователи: search, roles, status, sort.
- Роли: search, sort.
- Сотрудники: search, status, sort.
- Филиалы: name, responsible_employee_id, is_active.
- Объекты: q, branch_id, operating_organization_id, responsible_employee_id, date ranges, sort.
- Документы: q, document_type_id, status, organization_id, branch_id, responsible_employee_id, confidential/signed/renewal/file flags, date ranges, sort.
- Оборудование: search, type, status, condition, branch, responsible, verification/calibration status, availability, presets, date ranges, sort.
- Журналы и отчеты: date ranges, status, object, employee, and scenario-specific flags.
- Очереди: search, status, channel, priority, sort.

### UI-паттерны списков
- Управление фильтрами через `router.get(..., { preserveState: true, replace: true })`.
- Изменение страниц через query `page`.
- Подсветка статусов badge-ами.
- Источник: почти все `Index.vue`.

## 10. Проверки доступа на маршрутах

### Backend
- `auth` middleware почти на всех маршрутах, кроме `/login`, `/logout`, `/files/{token}` и `/api/user`.
- `authorizeResource` используется для ряда CRUD-контроллеров.
- `policy` и `Gate::authorize` применяются для:
  - users;
  - roles;
  - employees;
  - branches;
  - objects;
  - documents;
  - equipment;
  - organization-related entities;
  - shifts/reports;
  - queue operations.
- В некоторых местах используется явное `abort_unless(..., 403)`.
- `CheckUserStatus` разлогинивает заблокированного пользователя и редиректит на login.
- Источник: `bootstrap/app.php`, `CheckUserStatus.php`, контроллеры и policies.

### Frontend
- `useAuth()` читает `auth.user.roles` и `auth.user.permissions` из shared Inertia props.
- `Sidebar.vue` скрывает пункты меню по permissions и иногда по role `admin`.
- `Can.vue` скрывает кнопки и модалки по permission/role.
- Источник: `HandleInertiaRequests.php`, `useAuth.ts`, `Sidebar.vue`, `Can.vue`.

### Ключевые role/permission паттерны
- `admin` - видит блоки отчетов и журналов в меню.
- `lab` - попадает в `my-reports` как lab отчет.
- `decoder` - попадает в `my-reports` как decoder отчет.
- Наличие `employee_id` определяет доступ к `my-profile`.

## 11. Незавершенные маршруты и страницы

- `UserController` содержит методы `show`, `profile`, `updateProfile`, `changePassword` с `TODO`, но в маршрутах они не используются.
  - Источник: `UserController.php`, `routes/web.php`.
- `IndexController` и `resources/js/Pages/Index.vue` выглядят как демо-страница/пример Inertia layout и не привязаны к реальному маршруту.
  - Источник: `IndexController.php`, `routes/web.php`, `resources/js/Pages/Index.vue`.
- `resources/js/Pages/PageTemplate.vue` - демонстрационная страница layout/actions, не используется маршрутом.
  - Источник: `PageTemplate.vue`.
- `QueueController` и `Queues/Index.vue` содержат явные расхождения:
  - frontend вызывает `/queues/deliveries/{id}/retry`, но backend маршрут объявлен как `/queues/notification-deliveries/{id}/retry`;
  - frontend вызывает `/queues/failed-jobs/retry-all` и `/queues/failed-jobs/delete-all`, но соответствующих маршрутов в `routes/web.php` нет.
  - Источник: `QueueController.php`, `Queues/Index.vue`, `routes/web.php`.
- `DocumentController` имеет backend-роуты `addFile`, `addRelation`, `addVersion`, но в текущем frontend не найден явный прямой вызов этих endpoint-ов.
  - Источник: `DocumentController.php`, `DocumentForm.vue`, `routes/web.php`.
- `OrganizationController::restore` route есть, но явная кнопка восстановления в UI не найдена.
  - Источник: `Organizations/Index.vue`, `Organizations/Show.vue`, `OrganizationController.php`, `routes/web.php`.
- `PositionService` содержит `TODO` о связи с пользователями, поэтому часть подсчетов/ограничений пока не доведена до конца.
  - Источник: `PositionService.php`, `Positions/Index.vue`.

## 12. Неиспользуемые или спорные endpoint-ы

- `GET /api/user` - стандартный Sanctum endpoint; в текущем frontend прямого вызова не найдено.
- `GET /files/{token}` - служебный endpoint, используется опосредованно через `FileStorageService::getPublicUrl()`.
- `GET /exports/examples/*` - демонстрационные экспортные примеры.
- `IndexController` и `resources/js/Pages/Index.vue` - вероятно demo / unused.
- `resources/js/Pages/PageTemplate.vue` - demo / unused.
- `BranchController@show` возвращает JSON, хотя в меню и навигации это не отдельная страница.
- `UserController` placeholder-методы без route binding.
- `DocumentController` supplemental routes без прямого UI-entry.

## 13. Расхождения между frontend и backend

- `QueueController`:
  - frontend actions и backend routes не совпадают по пути и по наличию bulk-операций.
  - это главный найденный mismatch.
- `Documents`:
  - в UI есть вложенная форма, которая умеет собирать файлы/связи/версии в одном запросе;
  - backend дополнительно держит отдельные маршруты `files.store`, `relations.store`, `versions.store`;
  - без доп. проверки нельзя считать эти endpoint-ы полностью завершенными в плане UI-поддержки.
- `Users`:
  - есть route `GET /users/{user}/edit` для модалки, но `show`-сценария нет.
  - `UserController` содержит неиспользуемые методы, создающие ощущение незавершенного user profile flow.
- `Organizations`:
  - backend поддерживает restore, но UI-кнопка не обнаружена.
- `IndexController`/`Index.vue`:
  - примерная страница существует, но не включена в реальную навигацию.

## 14. Что можно использовать в новой системе

- Схему навигации:
  - dashboard как центр быстрых действий;
  - справочники отдельно;
  - рабочие журналы отдельно;
  - отчеты отдельно;
  - личные журналы через профиль.
- Паттерн "index + filters + table + modal edit/create + JSON edit endpoint".
- Паттерн "show page с вкладками" для объектов, организаций, сотрудников, документов, оборудования.
- Паттерн "upload file -> get signed URL -> store path in model".
- Паттерн `Can` + `Sidebar` + permissions from shared props.
- Паттерн `my-reports` по роли.
- Паттерн журналов смен и drill-down в детальный отчет.
- Источник: большинство изученных контроллеров и страниц.

## 15. Что нельзя переносить без проверки

- Mismatch очередей и bulk retry/delete-all endpoint-ов.
- `TODO`-методы `UserController`.
- Демо-страницы `Index.vue` и `PageTemplate.vue`.
- Неиспользуемые supplemental document routes без явного UI.
- Restore-кнопки и soft-delete flows, если в новой версии нужно строгое соответствие интерфейсу.
- Прямое смешение demo export example route group с боевыми разделами.
- Источник: `QueueController`, `UserController`, `IndexController`, `PageTemplate.vue`, `DocumentController`, `OrganizationController`.
