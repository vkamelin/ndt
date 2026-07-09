@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Профиль</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $user->name }}</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600">{{ $user->email }}</p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <div class="panel p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Основные данные</p>
                <dl class="mt-4 space-y-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Статус</dt>
                        <dd class="font-medium">{{ $user->status->label() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Email подтвержден</dt>
                        <dd class="font-medium">{{ $user->email_verified_at ? 'Да' : 'Нет' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="panel p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Роли и права</p>
                <div class="mt-4 space-y-4 text-sm text-slate-700">
                    <div>
                        <p class="text-slate-500">Роли</p>
                        <p class="mt-1 font-medium">
                            {{ $user->getRoleNames()->join(', ') ?: 'Нет ролей' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500">Права</p>
                        <p class="mt-1 font-medium">
                            {{ $user->getAllPermissions()->pluck('name')->join(', ') ?: 'Нет прав' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
