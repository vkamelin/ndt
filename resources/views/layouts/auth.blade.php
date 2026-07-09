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
</head>
<body class="bg-slate-50 text-slate-900">
    <main class="app-shell flex min-h-screen items-center justify-center py-12">
        <div class="panel w-full max-w-md overflow-hidden">
            <div class="border-b border-slate-200/70 px-8 py-6">
                <p class="panel-title">Вход</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Доступ к системе</h1>
            </div>

            <div class="px-8 py-8">
                @yield('content')
            </div>
        </div>
    </main>
</body>
</html>
