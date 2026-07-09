@extends('layouts.auth')

@section('content')
    <form method="post" action="{{ route('login') }}" class="space-y-5">
        @csrf

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-slate-700">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
            >
            @error('email')
                <p class="text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="password" class="text-sm font-medium text-slate-700">Пароль</label>
            <input
                id="password"
                name="password"
                type="password"
                required
                class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100"
            >
            @error('password')
                <p class="text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-3 text-sm text-slate-600">
            <input
                type="checkbox"
                name="remember"
                value="1"
                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100"
                @checked(old('remember'))
            >
            Запомнить меня
        </label>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">
            Войти
        </button>
    </form>
@endsection
