@extends('layouts.guest')

@section('content')
    <div class="space-y-6">
        <p class="text-base leading-7 text-slate-600">
            Каркас подготовлен для дальнейшей реализации модульного монолита на Laravel 12.
            На этом этапе заложены базовые layout’ы, Vite, Tailwind, Alpine.js, Sanctum,
            Spatie Permission, Spatie Activitylog и окружение для разработки.
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">Что уже есть</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Laravel-совместимый entrypoint</li>
                    <li>Структура `app/Modules`</li>
                    <li>Базовый рабочий layout</li>
                    <li>Конфиги для Vite и Tailwind</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">Следующий шаг</p>
                <p class="mt-3 text-sm text-slate-600">
                    После установки зависимостей можно будет переходить к Auth, ролям,
                    audit log и остальным доменным модулям.
                </p>
            </div>
        </div>
    </div>
@endsection

