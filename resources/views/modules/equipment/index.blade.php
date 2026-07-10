@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Оборудование</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Оборудование</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточки оборудования, поверки, калибровки, ремонты, выдачи, возвраты, перемещения, дефекты и документы.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @can('equipment.manage')
            <div class="panel p-6">
                <form method="post" action="{{ route('admin.equipment.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="equipment_type_id">Тип</label>
                        <select id="equipment_type_id" name="equipment_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($equipmentTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                        <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}">{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="name">Наименование</label>
                        <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="inventory_number">Инвентарный номер</label>
                        <input id="inventory_number" name="inventory_number" value="{{ old('inventory_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="serial_number">Серийный номер</label>
                        <input id="serial_number" name="serial_number" value="{{ old('serial_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                        <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="manufacturer">Производитель</label>
                        <input id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="model">Модель</label>
                        <input id="model" name="model" value="{{ old('model') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="purchased_at">Дата покупки</label>
                        <input id="purchased_at" type="date" name="purchased_at" value="{{ old('purchased_at') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="md:col-span-2 xl:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Добавить оборудование</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Оборудование</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Тип</th>
                            <th class="px-6 py-4">Номера</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($equipment as $item)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $item->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $item->manufacturer ?: 'Без производителя' }}{{ $item->model ? ' · '.$item->model : '' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $item->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $item->object?->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $item->type?->name }}</td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $item->inventory_number ?: 'Без инвентарного номера' }}</p>
                                    <p class="mt-1 text-slate-500">{{ $item->serial_number ?: 'Без серийного номера' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $item->status->isUsable() ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $item->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.equipment.show', $item) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700">Открыть</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $equipment->links() }}
    </div>
@endsection
