@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Администрирование</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $user->name }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка пользователя, его данных, роли и статуса доступа.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.3fr,1fr]">
            <div class="panel p-6 space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Email</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $user->email }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $user->status->isActive() ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $user->status->label() }}
                            </span>
                        </p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Роли</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $user->getRoleNames()->join(', ') ?: 'Нет ролей' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Связанный сотрудник</p>
                        @php($primaryEmployee = $user->primaryEmployee())
                        <p class="mt-2 text-sm font-medium text-slate-900">
                            {{ $primaryEmployee?->fullName() ?: 'Не привязан' }}
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <h2 class="text-lg font-semibold text-slate-900">Связанный сотрудник</h2>
                    @if ($primaryEmployee !== null)
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">{{ $primaryEmployee->fullName() }}</p>
                                    <p class="mt-1 text-sm text-slate-600">{{ $primaryEmployee->position?->name ?: 'Должность не указана' }}</p>
                                </div>
                                <div class="text-right text-sm text-slate-600">
                                    <p>{{ $primaryEmployee->object?->name ?: 'Объект не указан' }}</p>
                                    <p>{{ $primaryEmployee->object?->city?->name ?: 'Город не указан' }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-slate-600">Сотрудник не привязан.</p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                @can('update', $user)
                    <div class="panel p-6 space-y-6">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Данные пользователя</h2>
                            <p class="mt-2 text-sm text-slate-600">Администратор может изменить учетные данные, статус и привязку к сотруднику.</p>
                        </div>

                        <form method="post" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                            @csrf
                            @method('patch')

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" for="name">Имя</label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', $user->name) }}"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                >
                                @error('name')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" for="email">Email</label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    value="{{ old('email', $user->email) }}"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                >
                                @error('email')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" for="status">Статус</label>
                                <select
                                    id="status"
                                    name="status"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                >
                                    <option value="active" @selected(old('status', $user->status->value) === 'active')>Активен</option>
                                    <option value="blocked" @selected(old('status', $user->status->value) === 'blocked')>Заблокирован</option>
                                </select>
                                @error('status')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" for="employee_id">Связанный сотрудник</label>
                                <select
                                    id="employee_id"
                                    name="employee_id"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                >
                                    <option value="">Не привязан</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected((string) old('employee_id', $primaryEmployee?->id) === (string) $employee->id)>
                                            {{ $employee->fullName() }} — {{ $employee->position?->name ?: 'Должность не указана' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" for="password">Новый пароль</label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="new-password"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                >
                                @error('password')
                                    <p class="text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" for="password_confirmation">Подтверждение пароля</label>
                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                >
                            </div>

                            <button type="submit" class="inline-flex items-center rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">
                                Сохранить данные пользователя
                            </button>
                        </form>
                    </div>
                @endcan

                <div class="panel p-6 space-y-6">
                    @can('assignRoles', $user)
                        <form method="post" action="{{ route('admin.users.roles.update', $user) }}" class="space-y-4">
                            @csrf
                            @method('patch')

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500" for="roles">Изменить роли</label>
                                <select
                                    id="roles"
                                    name="roles[]"
                                    multiple
                                    class="min-h-40 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                >
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @error('roles')
                                <p class="text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                            @error('roles.*')
                                <p class="text-sm text-rose-600">{{ $message }}</p>
                            @enderror

                            <button type="submit" class="inline-flex items-center rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">
                                Сохранить роли
                            </button>
                        </form>
                    @endcan

                    @can('block', $user)
                        <div class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <h2 class="text-lg font-semibold text-slate-900">Статус доступа</h2>

                            @if ($user->status->isBlocked())
                                <form method="post" action="{{ route('admin.users.unblock', $user) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="rounded-full border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-50">
                                        Разблокировать
                                    </button>
                                </form>
                            @else
                                <form method="post" action="{{ route('admin.users.block', $user) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">
                                        Заблокировать
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
