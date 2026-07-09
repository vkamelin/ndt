@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Администрирование</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Пользователи</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Управление ролями, блокировкой и базовым доступом пользователей.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Пользователь</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Роли</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($users as $user)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $user->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $user->email }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $user->status->isActive() ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $user->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="space-y-3">
                                        <p class="text-slate-600">
                                            {{ $user->getRoleNames()->join(', ') ?: 'Нет ролей' }}
                                        </p>

                                        <form method="post" action="{{ route('admin.users.roles.update', $user) }}" class="space-y-3">
                                            @csrf
                                            @method('patch')

                                            <label class="block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Изменить роли</label>
                                            <select
                                                name="roles[]"
                                                multiple
                                                class="min-h-32 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
                                            >
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>
                                                        {{ $role->name }}
                                                    </option>
                                                @endforeach
                                            </select>

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
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
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
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $users->links() }}
    </div>
@endsection
