<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NDT Web Application') }}</title>
    @if (is_file(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="min-h-screen">
        <header class="border-b border-slate-200/70 bg-white/90 backdrop-blur">
            <div class="app-shell flex items-center justify-between gap-6 py-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">NDT</p>
                    <p class="text-lg font-semibold text-slate-900">Web Application</p>
                </div>

                <nav class="flex items-center gap-3 text-sm font-medium text-slate-600">
                    <a href="{{ route('home') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Главная</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full bg-brand-50 px-4 py-2 text-brand-700 transition hover:bg-brand-100">Рабочий стол</a>
                        <a href="{{ route('profile.show') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Профиль</a>
                        @can('users.view')
                            <a href="{{ route('admin.users.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Пользователи</a>
                        @endcan
                        @can('cities.view_any')
                            <a href="{{ route('admin.cities.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Города</a>
                        @endcan
                        @can('objects.view_any')
                            <a href="{{ route('admin.objects.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Объекты</a>
                        @endcan
                        @can('employees.view_any')
                            <a href="{{ route('admin.employees.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Сотрудники</a>
                        @endcan
                        @can('organizations.view_any')
                            <a href="{{ route('admin.organizations.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Организации</a>
                        @endcan
                        @can('welds.view_any')
                            <a href="{{ route('admin.welds.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Стыки</a>
                        @endcan
                        @can('ndt_requests.view_any')
                            <a href="{{ route('admin.ndt-requests.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Заявки НК</a>
                        @endcan
                        @can('positions.manage')
                            <a href="{{ route('admin.positions.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Должности</a>
                        @endcan
                        @can('directories.manage')
                            <a href="{{ route('admin.dictionaries.overview') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Справочники</a>
                        @endcan
                        @can('viewAny', \App\Modules\Audit\Models\AuditLog::class)
                            <a href="{{ route('admin.audit-logs.index') }}" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Журнал аудита</a>
                        @endcan
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full px-4 py-2 transition hover:bg-slate-100 hover:text-slate-900">Выйти</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full bg-brand-50 px-4 py-2 text-brand-700 transition hover:bg-brand-100">Войти</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="app-shell py-8">
            @yield('content')
        </main>
    </div>
    @livewireScripts
</body>
</html>
