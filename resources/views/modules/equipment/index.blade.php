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
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Добавление оборудования</h2>
                        <p class="mt-2 text-sm text-slate-600">Большая форма перенесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.equipment.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить оборудование</a>
                </div>
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
