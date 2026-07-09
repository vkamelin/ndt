@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Рабочий стол</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Базовый экран приложения</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Здесь будет размещаться основной интерфейс системы после реализации
                авторизации, ролей и доменных модулей. На текущем этапе это только
                нейтральная рабочая оболочка.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Стек</p>
                <p class="mt-2 text-lg font-semibold">PHP 8.4 / Laravel 12</p>
            </div>
            <div class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Frontend</p>
                <p class="mt-2 text-lg font-semibold">Blade / Livewire / Alpine</p>
            </div>
            <div class="panel p-5">
                <p class="text-sm font-medium text-slate-500">Infra</p>
                <p class="mt-2 text-lg font-semibold">MySQL / Redis / Vite</p>
            </div>
        </div>
    </div>
@endsection

