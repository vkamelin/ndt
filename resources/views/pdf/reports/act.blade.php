Акт {{ $act->number }}
Дата: {{ $act->date?->format('d.m.Y') }}
Тип: {{ $act->type?->name ?? 'Не указан' }}
Реестр: {{ $act->register?->number ?? 'Не указан' }}
Город: {{ $act->city?->name ?? 'Не указан' }}
Объект: {{ $act->object?->name ?? 'Не указан' }}
Комментарий: {{ $act->comment ?? 'Нет' }}
