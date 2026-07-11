<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'NDT') }}</title>
    @if (is_file(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900">
    <div
        class="min-h-screen"
        x-data="{ menuOpen: false }"
        x-effect="document.documentElement.classList.toggle('overflow-hidden', menuOpen)"
        @keydown.escape.window="menuOpen = false"
    >
        @auth
            <aside class="hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-30 lg:flex lg:w-80 lg:flex-col lg:border-r lg:border-slate-200/70 lg:bg-white/90 lg:backdrop-blur">
                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-5">
                    @include('layouts.partials.navigation-menu')
                </div>
            </aside>
        @endauth

        <div class="min-h-screen lg:pl-80">
            <header class="sticky top-0 z-20 border-b border-slate-200/70 bg-white/90 backdrop-blur">
                <div class="app-shell flex items-center justify-between gap-3 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        @auth
                            <button
                                type="button"
                                @click="menuOpen = true"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 lg:hidden"
                                aria-controls="mobile-navigation"
                                :aria-expanded="menuOpen.toString()"
                                aria-label="Открыть меню"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M3 6h14M3 10h14M3 14h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        @endauth

                        <a href="{{ route('home') }}" class="min-w-0">
                            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">NDT</p>
                        </a>
                    </div>

                    <div class="flex items-center gap-2">
                        @auth
                            <div class="hidden items-center gap-2 sm:flex">
                                @can('notifications.view_own')
                                    <a href="{{ route('notifications.index') }}" class="rounded-full px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                                        Уведомления
                                        <span class="ml-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-700">
                                            {{ auth()->user()->systemNotifications()->whereNull('read_at')->count() }}
                                        </span>
                                    </a>
                                @endcan
                                <a href="{{ route('profile.show') }}" class="rounded-full px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Профиль</a>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                Войти
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            @auth
                <div
                    x-cloak
                    x-show="menuOpen"
                    x-transition.opacity
                    class="fixed inset-0 z-40 lg:hidden"
                    aria-hidden="true"
                >
                    <button
                        type="button"
                        class="absolute inset-0 bg-slate-950/40"
                        @click="menuOpen = false"
                        aria-label="Закрыть меню"
                    ></button>

                    <aside
                        id="mobile-navigation"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="-translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="-translate-x-full"
                        class="absolute inset-y-0 left-0 flex h-full w-[min(100%-1rem,20rem)] flex-col overflow-hidden border-r border-slate-200 bg-white shadow-2xl"
                        role="dialog"
                        aria-modal="true"
                        aria-label="Основное меню"
                    >
                        <div class="flex items-center justify-between border-b border-slate-200/70 px-4 py-4">
                            <div class="min-w-0">
                                <p class="panel-title">Навигация</p>
                                <p class="mt-1 truncate text-lg font-semibold text-slate-900">Меню</p>
                            </div>
                            <button
                                type="button"
                                @click="menuOpen = false"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                                aria-label="Закрыть меню"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                    <path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto px-4 py-5">
                            @include('layouts.partials.navigation-menu')
                        </div>
                    </aside>
                </div>
            @endauth

            <main class="app-shell py-8">
                @yield('content')
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
