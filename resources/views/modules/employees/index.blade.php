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
                <form method="post" action="{{ route('admin.employees.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                        <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="position_id">Должность</label>
                        <select id="position_id" name="position_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="user_id">Пользователь</label>
                        <select id="user_id" name="user_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Не привязан</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="last_name">Фамилия</label>
                        <input id="last_name" name="last_name" value="{{ old('last_name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="first_name">Имя</label>
                        <input id="first_name" name="first_name" value="{{ old('first_name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="middle_name">Отчество</label>
                        <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="phone">Телефон</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="personnel_number">Табельный номер</label>
                        <input id="personnel_number" name="personnel_number" value="{{ old('personnel_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                        <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="active" @selected(old('status', 'active') === 'active')>Активен</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Не активен</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить сотрудника</button>
                    </div>
                </form>
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
