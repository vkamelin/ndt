{{ $reportTitle ?? 'Смена лаборанта' }}
Сотрудник: {{ $shift->employee?->fullName() ?? 'Не указан' }}
Объект: {{ $shift->object?->name ?? 'Не указан' }}
Город: {{ $shift->object?->city?->name ?? 'Не указан' }}
Статус: {{ $shift->status->label() }}
Старт: {{ $shift->started_at?->format('d.m.Y H:i') ?? 'Не указан' }}
Завершение: {{ $shift->finished_at?->format('d.m.Y H:i') ?? 'Не завершена' }}

Регламентные работы: {{ $shift->labRegulatoryWorks->count() }}
Движения пленки: {{ $shift->filmTransactions->count() }}
Движения химии: {{ $shift->chemicalTransactions->count() }}
Запросы химии: {{ $shift->chemicalRequests->count() }}

Сводка:
{{ $shift->labReport?->summary ?? 'Отчет отсутствует' }}
