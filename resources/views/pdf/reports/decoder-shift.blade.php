{{ $reportTitle ?? 'Смена дешифровщика' }}
Сотрудник: {{ $shift->employee?->fullName() ?? 'Не указан' }}
Объект: {{ $shift->object?->name ?? 'Не указан' }}
Город: {{ $shift->object?->city?->name ?? 'Не указан' }}
Статус: {{ $shift->status->label() }}
Старт: {{ $shift->started_at?->format('d.m.Y H:i') ?? 'Не указан' }}
Завершение: {{ $shift->finished_at?->format('d.m.Y H:i') ?? 'Не завершена' }}

Просмотренные группы: {{ $shift->decoderFilmGroups->count() }}
Брак: {{ $shift->decoderRejects->count() }}
Подозрения на подлог: {{ $shift->decoderForgerySuspicion->count() }}
Очистки рабочего места: {{ $shift->decoderCleanups->count() }}
Дешифровки: {{ $shift->decoderDecryptions->count() }}

Сводка:
{{ $shift->decoderReport?->summary ?? 'Отчет отсутствует' }}
