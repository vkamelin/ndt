<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NDT Web Application') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-brand-50 px-4 py-2 text-brand-700 transition hover:bg-brand-100">Рабочий стол</a>
                </nav>
            </div>
        </header>

        <main class="app-shell py-8">
            @yield('content')
        </main>
    </div>
</body>
</html>

