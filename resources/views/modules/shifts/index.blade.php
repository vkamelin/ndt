@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Смены</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Смены лаборанта и дешифровщика</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Раздел для открытия смен, журналов операций, отчетов и завершения смен.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @can('shifts.manage')
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Открытие смены</h2>
                        <p class="mt-2 text-sm text-slate-600">Форма перенесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.shifts.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Открыть смену</a>
                </div>
            </div>
        @endcan

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.shifts.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="type_filter">Тип</label>
                    <select id="type_filter" name="type" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все типы</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status_filter">Статус</label>
                    <select id="status_filter" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все статусы</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Применить</button>
                </div>
            </form>
        </div>

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Сотрудник</th>
                            <th class="px-6 py-4">Тип</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Старт</th>
                            <th class="px-6 py-4">Финиш</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($shifts as $shift)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $shift->employee?->fullName() }}</p>
                                    <p class="mt-1 text-slate-500">{{ $shift->employee?->object?->city?->name }} · {{ $shift->employee?->object?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $shift->type->label() }}</td>
                                <td class="px-6 py-5">{{ $shift->status->label() }}</td>
                                <td class="px-6 py-5">{{ $shift->started_at?->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-5">{{ $shift->finished_at?->format('d.m.Y H:i') ?: 'Не завершена' }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.shifts.show', $shift) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700">Открыть</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $shifts->links() }}
    </div>
@endsection
