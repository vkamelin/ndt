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
    <main class="app-shell flex min-h-screen items-center justify-center py-12">
        <div class="panel w-full max-w-3xl overflow-hidden">
            <div class="border-b border-slate-200/70 px-8 py-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="panel-title">NDT Web Application</p>
                        <h1 class="mt-2 text-2xl font-semibold text-slate-900">Базовый каркас Laravel-проекта</h1>
                    </div>
                    <span class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700">
                        Этап 1
                    </span>
                </div>
            </div>

            <div class="px-8 py-8">
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>

