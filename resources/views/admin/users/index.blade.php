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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($users as $user)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-slate-900 transition hover:text-brand-700">
                                        {{ $user->name }}
                                    </a>
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
