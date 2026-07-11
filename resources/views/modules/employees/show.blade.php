@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Сотрудники</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $employee->fullName() }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка сотрудника с привязкой к объекту/участку, должности, пользователю и квалификациям.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.6fr,1fr]">
            @can('employees.manage')
                <div class="panel p-6 space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">Редактирование сотрудника</p>
                                <p class="mt-1 text-sm text-slate-600">Основная форма перенесена на отдельную страницу.</p>
                            </div>
                            <a href="{{ route('admin.employees.edit', $employee) }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Редактировать сотрудника</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="panel p-6">
                    <p class="text-sm text-slate-600">Редактирование доступно только пользователям с правом управления сотрудниками.</p>
                </div>
            @endcan

            <div class="panel p-6 space-y-3">
                <h2 class="text-xl font-semibold text-slate-900">Сводка</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Город</dt>
                        <dd class="font-medium text-slate-900">{{ $employee->object?->city?->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Объект/участок</dt>
                        <dd class="font-medium text-slate-900">{{ $employee->object?->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Должность</dt>
                        <dd class="font-medium text-slate-900">{{ $employee->position?->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Пользователь</dt>
                        <dd class="font-medium text-slate-900">{{ $employee->users->first()?->email ?: 'Не привязан' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="panel p-6 space-y-6">
            <div>
                <h2 class="text-2xl font-semibold text-slate-900">Квалификации</h2>
                <p class="mt-2 text-sm text-slate-600">Квалификации по методам НК привязаны к сотруднику.</p>
            </div>

            @can('employees.manage')
                <form method="post" action="{{ route('admin.employees.qualifications.store', $employee) }}" class="grid gap-4 md:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="method">Метод</label>
                        <select id="method" name="method" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach (\App\Modules\Employees\Enums\QualificationMethod::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="valid_until">Действует до</label>
                        <input id="valid_until" name="valid_until" type="date" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <input id="comment" name="comment" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить квалификацию</button>
                    </div>
                </form>
            @endcan

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Метод</th>
                            <th class="px-6 py-4">Действует до</th>
                            <th class="px-6 py-4">Комментарий</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($employee->qualifications as $qualification)
                            <tr>
                                <td class="px-6 py-5 font-medium text-slate-900">
                                    {{ \App\Modules\Employees\Enums\QualificationMethod::from($qualification->method->value)->label() }}
                                </td>
                                <td class="px-6 py-5">{{ $qualification->valid_until?->format('d.m.Y') ?: 'Не ограничено' }}</td>
                                <td class="px-6 py-5">{{ $qualification->comment ?: '—' }}</td>
                                <td class="px-6 py-5">
                                    @can('employees.manage')
                                        <form method="post" action="{{ route('admin.employees.qualifications.destroy', [$employee, $qualification]) }}">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">
                                                Удалить
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-500">Только просмотр</span>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
