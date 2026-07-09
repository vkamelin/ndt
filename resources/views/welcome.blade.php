@extends('layouts.guest')

@section('content')
    <div class="space-y-6">
        <p class="text-base leading-7 text-slate-600">
            Каркас подготовлен для дальнейшей реализации модульного монолита на Laravel 12.
            На этом этапе уже доступны вход, роли, permissions и защита по статусу
            пользователя.
            Базовый каркас Laravel-проекта сохранен и расширен рабочим auth-контуром.
        </p>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('login') }}" class="inline-flex items-center rounded-full bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">
                Войти в систему
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">Что уже есть</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-600">
                    <li>Вход по email и паролю</li>
                    <li>Роли и permissions</li>
                    <li>Блокировка пользователя по статусу</li>
                    <li>Базовая страница профиля</li>
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <p class="text-sm font-semibold text-slate-900">Следующий шаг</p>
                <p class="mt-3 text-sm text-slate-600">
                    После этого этапа можно переходить к объектам, сотрудникам и
                    остальным доменным модулям.
                </p>
            </div>
        </div>
    </div>
@endsection
