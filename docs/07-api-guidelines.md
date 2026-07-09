# API guidelines

## 1. Назначение

API нужен для PWA/mobile-сценариев и возможных интеграций. Основной веб-интерфейс может работать через Blade/Livewire, но мобильные сценарии должны иметь JSON API.

## 2. Общие правила API

1. Использовать Laravel Sanctum.
2. Использовать rate limiting.
3. Проверять права через policies.
4. Использовать FormRequest или отдельную валидацию для входных данных.
5. Использовать DTO для сложных операций.
6. Возвращать единый формат ошибок.
7. Писать audit log для критичных действий.
8. Не отдавать приватные storage-пути.
9. Не обходить бизнес-сервисы прямыми обновлениями моделей.

## 3. Базовые группы endpoint-ов

- `/api/auth`;
- `/api/profile`;
- `/api/mobile/tasks`;
- `/api/mobile/shifts`;
- `/api/mobile/welds`;
- `/api/mobile/files`;
- `/api/mobile/equipment`;
- `/api/notifications`.

## 4. Формат успешного ответа

Рекомендуемый формат:

```json
{
  "data": {},
  "meta": {}
}
```

Для списков:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 100
  }
}
```

## 5. Формат ошибки

Рекомендуемый формат:

```json
{
  "message": "Описание ошибки",
  "errors": {}
}
```

Для validation error:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Сообщение ошибки"]
  }
}
```

## 6. Auth API

Группа `/api/auth`.

Возможные endpoint-ы:

- `POST /api/auth/login`;
- `POST /api/auth/logout`;
- `GET /api/profile`.

Не хранить токены без необходимости. Для PWA/mobile использовать Sanctum.

## 7. Profile API

Группа `/api/profile`.

Должна возвращать:

- пользователя;
- связанного сотрудника;
- роли;
- permissions;
- объект/участок закрепления;
- доступные действия.

## 8. Mobile tasks API

Группа `/api/mobile/tasks`.

Назначение: PWA-экран “Мои задания НК”.

Возможные endpoint-ы:

- `GET /api/mobile/tasks` — список заданий текущего пользователя;
- `GET /api/mobile/tasks/{task}` — карточка задания;
- `POST /api/mobile/tasks/{task}/accept` — принять задание;
- `POST /api/mobile/tasks/{task}/items/{item}/complete` — отметить выполнение по стыку;
- `POST /api/mobile/tasks/{task}/items/{item}/files` — загрузить файл;
- `POST /api/mobile/tasks/{task}/finish` — завершить задание.

Правила:

- пользователь видит только доступные ему задания;
- backend проверяет права независимо от UI;
- результаты критичных действий пишутся в audit log.

## 9. Mobile shifts API

Группа `/api/mobile/shifts`.

Общие endpoint-ы:

- `GET /api/mobile/shifts/current`;
- `POST /api/mobile/shifts/start`;
- `POST /api/mobile/shifts/{shift}/finish`.

### 9.1 Смена лаборанта

Возможные endpoint-ы:

- `POST /api/mobile/shifts/{shift}/lab/film-receive`;
- `POST /api/mobile/shifts/{shift}/lab/film-issue`;
- `POST /api/mobile/shifts/{shift}/lab/film-write-off`;
- `POST /api/mobile/shifts/{shift}/lab/chemical-request`;
- `POST /api/mobile/shifts/{shift}/lab/chemical-receive`;
- `POST /api/mobile/shifts/{shift}/lab/chemical-replace`;
- `POST /api/mobile/shifts/{shift}/lab/regulatory-work`;
- `POST /api/mobile/shifts/{shift}/lab/transfer-to-decoder`.

### 9.2 Смена дешифровщика

Возможные endpoint-ы:

- `GET /api/mobile/shifts/{shift}/decoder/queue`;
- `POST /api/mobile/shifts/{shift}/decoder/viewed-groups`;
- `POST /api/mobile/shifts/{shift}/decoder/rejects`;
- `POST /api/mobile/shifts/{shift}/decoder/forgery-suspicions`;
- `POST /api/mobile/shifts/{shift}/decoder/cleanup`;
- `POST /api/mobile/shifts/{shift}/decoder/decryptions`;
- `POST /api/mobile/shifts/{shift}/decoder/transfer-to-analysis`.

## 10. Mobile welds API

Группа `/api/mobile/welds`.

Возможные endpoint-ы:

- `GET /api/mobile/welds/{weld}` — карточка стыка;
- `GET /api/mobile/welds/search` — поиск стыка по номеру, баркоду, линии, чертежу;
- `GET /api/mobile/welds/{weld}/results` — результаты по стыку.

## 11. Mobile files API

Группа `/api/mobile/files`.

Правила:

- загрузка файлов через backend;
- скачивание только через backend;
- проверка прав по связанной сущности;
- не отдавать прямой storage path;
- использовать signed URL, если нужен временный URL.

Возможные endpoint-ы:

- `POST /api/mobile/files`;
- `GET /api/mobile/files/{file}/download`;
- `DELETE /api/mobile/files/{file}` — только при наличии права и audit log.

## 12. Notifications API

Группа `/api/notifications`.

Возможные endpoint-ы:

- `GET /api/notifications`;
- `POST /api/notifications/{notification}/read`;
- `POST /api/notifications/read-all`.

## 13. Версионирование API

На первом этапе версионирование можно не вводить в URL, если нет внешних потребителей.

Если появятся внешние интеграции, использовать `/api/v1/...`.

## 14. Запреты

Не делать:

- API, обходящий policies;
- API, который редактирует утвержденное заключение напрямую;
- API, который отдает приватный файл без проверки прав;
- API, который меняет статус без audit log;
- API, который возвращает все записи без пагинации;
- API, который принимает произвольные поля без DTO/FormRequest.
