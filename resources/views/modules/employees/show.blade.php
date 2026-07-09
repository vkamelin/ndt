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
                    <form method="post" action="{{ route('admin.employees.update', $employee) }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected(old('object_id', $employee->object_id) == $object->id)>{{ $object->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="position_id">Должность</label>
                            <select id="position_id" name="position_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected(old('position_id', $employee->position_id) == $position->id)>{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="user_id">Пользователь</label>
                            <select id="user_id" name="user_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не привязан</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(old('user_id', $employee->users->first()?->id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="personnel_number">Табельный номер</label>
                            <input id="personnel_number" name="personnel_number" value="{{ old('personnel_number', $employee->personnel_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="last_name">Фамилия</label>
                            <input id="last_name" name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="first_name">Имя</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="middle_name">Отчество</label>
                            <input id="middle_name" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="phone">Телефон</label>
                            <input id="phone" name="phone" value="{{ old('phone', $employee->phone) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="email">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $employee->email) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                            <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="active" @selected(old('status', $employee->status->value) === 'active')>Активен</option>
                                <option value="inactive" @selected(old('status', $employee->status->value) === 'inactive')>Не активен</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить сотрудника</button>
                        </div>
                    </form>
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
