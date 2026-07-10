Заключение {{ $conclusion->number }}
Дата: {{ $conclusion->date?->format('d.m.Y') }}
Объект: {{ $conclusion->object?->name ?? 'Не указан' }}
Город: {{ $conclusion->object?->city?->name ?? 'Не указан' }}
Метод: {{ $conclusion->method?->name ?? 'Не указан' }}
Статус: {{ $conclusion->status->label() }}
Подготовил: {{ $conclusion->preparedBy?->fullName() ?? 'Не указан' }}
Проверил: {{ $conclusion->checkedBy?->fullName() ?? 'Не указан' }}
Утвердил: {{ $conclusion->approvedBy?->fullName() ?? 'Не указан' }}

Позиции:
@forelse ($conclusion->items as $item)
{{ $item->sort_order }}. Стык {{ $item->result?->weld?->weld_number ?? 'Не указан' }}, метод {{ $item->result?->method?->name ?? 'Не указан' }}, результат {{ $item->result?->status->label() ?? 'Не указан' }}
@empty
Позиции отсутствуют.
@endforelse
