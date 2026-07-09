# Архитектура приложения

## 1. Тип архитектуры

Приложение строится как модульный монолит на Laravel.

Базовый стек:

- PHP 8.4;
- Laravel 12;
- MySQL 8.4;
- Redis;
- Laravel Queue;
- Laravel Scheduler;
- Supervisor;
- Laravel Sanctum;
- Spatie Laravel Permission;
- Spatie Laravel Activitylog или собственный audit log;
- Blade;
- Livewire 3;
- Alpine.js;
- Vite;
- Tailwind CSS.

Не использовать как базовое решение:

- микросервисы;
- Kubernetes;
- Octane;
- Swoole;
- GraphQL;
- Elasticsearch;
- MongoDB;
- обязательный Vue frontend;
- обязательное нативное Android-приложение.

## 2. Основные слои

### HTTP-слой

HTTP-слой отвечает только за прием запроса и возврат ответа.

Допустимые элементы:

- Controllers;
- FormRequest;
- Middleware;
- Policies;
- Livewire components;
- Blade views;
- API Resources, если они нужны для JSON API.

Контроллер не должен содержать бизнес-логику.

Правильная схема:

```text
Request
→ FormRequest validation
→ Policy / Gate
→ Service
→ Response / Redirect / View / JSON
```

### Application-слой

Application-слой содержит сценарии использования системы.

Здесь должны находиться:

- сервисы создания заявок;
- сервисы назначения заданий;
- сервисы фиксации результатов;
- сервисы завершения смен;
- сервисы утверждения заключений;
- сервисы генерации документов;
- сервисы аудита критичных действий.

### Domain-слой

Domain-слой содержит предметные сущности, enum-классы, value objects и правила статусов.

Примеры:

- `NdtRequestStatus`;
- `WeldStatus`;
- `NdtTaskStatus`;
- `NdtResultStatus`;
- `ConclusionStatus`;
- `ShiftType`;
- `ShiftStatus`;
- `NdtMethodCode`.

### Infrastructure-слой

Infrastructure-слой отвечает за внешние и технические механизмы:

- хранение файлов;
- очереди;
- экспорт Excel;
- генерация PDF;
- отправка уведомлений;
- backup-скрипты;
- интеграции с S3-compatible хранилищем.

## 3. Модули

Ориентироваться на следующие backend-модули:

- `Auth`;
- `Access`;
- `Employees`;
- `Organizations`;
- `Objects`;
- `Welds`;
- `NdtRequests`;
- `NdtTasks`;
- `NdtResults`;
- `Radiography`;
- `VisualControl`;
- `PenetrantControl`;
- `MagneticControl`;
- `UltrasonicControl`;
- `Conclusions`;
- `Registers`;
- `Shifts`;
- `Inventory`;
- `Equipment`;
- `Documents`;
- `Notifications`;
- `Audit`;
- `Reports`;
- `Admin`.

## 4. Правила зависимости модулей

1. Модуль может читать данные другого модуля через модели или сервисы, если это необходимо для бизнес-сценария.
2. Модуль не должен менять внутреннее состояние соседнего модуля напрямую, если для этого есть профильный сервис.
3. Критичные операции должны выполняться через сервисы, а не через прямой `Model::update()` в контроллере.
4. Статусы должны меняться через методы/сервисы, где можно централизованно писать audit log.
5. Файлы должны сохраняться через единый файловый сервис.
6. Уведомления должны создаваться через единый сервис уведомлений или очередь.

## 5. Рекомендуемая структура модуля

Примерная структура:

```text
app/Modules/NdtRequests/
├── Actions/
├── DTO/
├── Enums/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Livewire/
├── Models/
├── Policies/
├── Services/
└── Support/
```

Структура может быть адаптирована под Laravel-проект, но смысловое разделение должно сохраняться.

## 6. Правила для frontend

Базовый frontend:

- Blade;
- Livewire 3;
- Alpine.js;
- Tailwind CSS.

Livewire-компонент может управлять состоянием формы или таблицы, но не должен содержать сложную бизнес-логику.

Бизнес-операции должны выполняться через backend-сервисы.

## 7. Очереди и фоновые задачи

Через очередь должны выполняться:

- тяжелые Excel-экспорты;
- генерация PDF;
- отправка уведомлений;
- обработка больших файлов;
- массовые операции;
- операции, которые могут выполняться дольше обычного HTTP-запроса.

## 8. Основные архитектурные запреты

Не делать:

- бизнес-логику в контроллерах;
- бизнес-логику во view;
- прямую отдачу приватных файлов из `public`;
- редактирование утвержденных заключений напрямую;
- хранение методов контроля строкой;
- массовое удаление производственных данных без audit log;
- сложные абстракции без подтвержденной необходимости.
