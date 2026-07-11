<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NDT Web Application') }} · PWA</title>
    @if (is_file(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="bg-slate-950 text-slate-100">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_35%),linear-gradient(180deg,_#0f172a_0%,_#020617_100%)]">
        <header class="border-b border-white/10 bg-slate-950/70 backdrop-blur">
            <div class="mx-auto flex w-full flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-sky-300/80">PWA</p>
                    <h1 class="text-xl font-semibold text-white">@yield('title', 'Рабочее место')</h1>
                </div>

                <nav class="flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('pwa.tasks') }}" class="rounded-full border border-white/10 px-4 py-2 text-slate-100 transition hover:border-sky-400/40 hover:bg-sky-400/10">Мои задания НК</a>
                    <a href="{{ route('pwa.lab-shift') }}" class="rounded-full border border-white/10 px-4 py-2 text-slate-100 transition hover:border-sky-400/40 hover:bg-sky-400/10">Смена лаборанта</a>
                    <a href="{{ route('pwa.decoder') }}" class="rounded-full border border-white/10 px-4 py-2 text-slate-100 transition hover:border-sky-400/40 hover:bg-sky-400/10">Дешифровка</a>
                    <a href="{{ route('pwa.control') }}" class="rounded-full border border-white/10 px-4 py-2 text-slate-100 transition hover:border-sky-400/40 hover:bg-sky-400/10">Контроль участка</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-400/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @livewireScripts
</body>
</html>
