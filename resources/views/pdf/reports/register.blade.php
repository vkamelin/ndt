Реестр {{ $register->number }}
Дата: {{ $register->date?->format('d.m.Y') }}
Тип: {{ $register->type?->name ?? 'Не указан' }}
Статус: {{ $register->status->label() }}
Город: {{ $register->city?->name ?? 'Не указан' }}
Объект: {{ $register->object?->name ?? 'Не указан' }}
Отправитель: {{ $register->senderEmployee?->fullName() ?? 'Не указан' }}
Получатель: {{ $register->receiverEmployee?->fullName() ?? 'Не указан' }}

Позиции:
@forelse ($register->items as $item)
{{ $item->sort_order }}. {{ class_basename($item->related_type) }} #{{ $item->related_id }} @if($item->comment) - {{ $item->comment }} @endif
@empty
Позиции отсутствуют.
@endforelse

Акты:
@forelse ($register->acts as $act)
{{ $act->number }} · {{ $act->type?->name ?? 'Не указан' }} · {{ $act->date?->format('d.m.Y') }}
@empty
Акты отсутствуют.
@endforelse
