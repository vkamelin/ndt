@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Стыки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $weld->weld_number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка стыка и назначение методов контроля.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.5fr,1fr]">
            <div class="panel p-6 space-y-4">
                @can('manage', $weld)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">Редактирование стыка</p>
                                <p class="mt-1 text-sm text-slate-600">Основная форма перенесена на отдельную страницу.</p>
                            </div>
                            <a href="{{ route('admin.welds.edit', $weld) }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Редактировать стык</a>
                        </div>
                    </div>
                @endcan

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Объект/участок</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $weld->object?->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $weld->object?->city?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $weld->status->label() }}</p>
                    </div>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Методы контроля</h2>
                @can('manage', $weld)
                    @can('weld_ndt_methods.manage')
                    <form method="post" action="{{ route('admin.welds.methods.sync', $weld) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            @php($selectedMethodIds = $weld->ndtMethods->pluck('id')->all())
                            @foreach ($methods as $method)
                                <label class="flex items-center gap-3 text-sm text-slate-700">
                                    <input type="checkbox" name="method_ids[]" value="{{ $method->id }}" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(collect($selectedMethodIds)->contains($method->id))>
                                    <span>{{ $method->code->label() }} {{ $method->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Обновить методы</button>
                    </form>
                    @endcan
                @endcan

                <div class="space-y-3">
                    @forelse ($weld->ndtMethods as $method)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <p class="font-medium text-slate-900">{{ $method->code->label() }} {{ $method->name }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Методы пока не назначены.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel p-6 space-y-4">
            <h2 class="text-2xl font-semibold text-slate-900">История статусов</h2>
            <div class="space-y-3">
                @foreach ($weld->statusHistory as $history)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="font-medium text-slate-900">{{ $history->from_status ?: '—' }} → {{ $history->to_status }}</p>
                            <p class="text-slate-500">{{ $history->created_at?->format('d.m.Y H:i') }}</p>
                        </div>
                        <p class="mt-2 text-slate-600">
                            {{ $history->changedBy?->name ?: 'Система' }}@if($history->comment) · {{ $history->comment }}@endif
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
