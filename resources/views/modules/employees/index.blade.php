@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Сотрудники</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Сотрудники</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Сотрудник закрепляется за объектом/участком и должностью.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @can('employees.manage')
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Добавление сотрудника</h2>
                        <p class="mt-2 text-sm text-slate-600">Форма вынесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.employees.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить сотрудника</a>
                </div>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Сотрудник</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Должность</th>
                            <th class="px-6 py-4">Пользователь</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($employees as $employee)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $employee->fullName() }}</p>
                                    <p class="mt-1 text-slate-500">{{ $employee->personnel_number ?: 'Без табельного номера' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $employee->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $employee->object?->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $employee->position?->name }}</td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $employee->users->first()?->name ?: 'Не привязан' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $employee->status->isActive() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $employee->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $employees->links() }}
    </div>
@endsection
