@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Рабочий стол</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Базовый экран приложения</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Здесь будет размещаться основной интерфейс системы после реализации
                доменных модулей. Сейчас доступ уже ограничен по ролям и статусу
                пользователя.
            </p>
            <p class="mt-4 text-sm font-medium text-slate-700">
                Вы вошли как {{ auth()->user()->name }}.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Стек</p>
                <p class="mt-2 text-lg font-semibold">PHP 8.4 / Laravel 12</p>
            </div>
            <div class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Доступ</p>
                <p class="mt-2 text-lg font-semibold">
                    {{ auth()->user()->getRoleNames()->join(', ') ?: 'Без роли' }}
                </p>
            </div>
            <div class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Статус</p>
                <p class="mt-2 text-lg font-semibold">{{ auth()->user()->status->label() }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-3">
            <div class="panel p-6 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="panel-title">Предупреждения</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Поверки</h2>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $expiringVerifications->count() }}</span>
                </div>
                <div class="space-y-3 text-sm">
                    @forelse ($expiringVerifications as $verification)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">{{ $verification->equipment?->name }}</p>
                            <p class="mt-1 text-slate-600">{{ $verification->equipment?->object?->city?->name }} · {{ $verification->equipment?->object?->name }}</p>
                            <p class="mt-1 text-slate-500">Действует до {{ $verification->valid_until?->format('d.m.Y') }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500">Нет истекающих поверок в ближайшее время.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="panel-title">Предупреждения</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Калибровки</h2>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $expiringCalibrations->count() }}</span>
                </div>
                <div class="space-y-3 text-sm">
                    @forelse ($expiringCalibrations as $calibration)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">{{ $calibration->equipment?->name }}</p>
                            <p class="mt-1 text-slate-600">{{ $calibration->equipment?->object?->city?->name }} · {{ $calibration->equipment?->object?->name }}</p>
                            <p class="mt-1 text-slate-500">Действует до {{ $calibration->valid_until?->format('d.m.Y') }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500">Нет истекающих калибровок в ближайшее время.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="panel-title">Предупреждения</p>
                        <h2 class="mt-2 text-2xl font-semibold text-slate-900">Квалификации</h2>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $expiringQualifications->count() }}</span>
                </div>
                <div class="space-y-3 text-sm">
                    <p class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-slate-600">
                        Строгий контроль квалификаций: {{ $strictQualificationGuard ? 'включен' : 'выключен' }}.
                    </p>
                    @forelse ($expiringQualifications as $qualification)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">{{ $qualification->employee?->fullName() }}</p>
                            <p class="mt-1 text-slate-600">{{ $qualification->employee?->object?->city?->name }} · {{ $qualification->employee?->object?->name }}</p>
                            <p class="mt-1 text-slate-500">{{ $qualification->method?->label() }} до {{ $qualification->valid_until?->format('d.m.Y') }}</p>
                        </div>
                    @empty
                        <p class="text-slate-500">Нет истекающих квалификаций в ближайшее время.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
