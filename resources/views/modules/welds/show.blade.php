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
                    <form method="post" action="{{ route('admin.welds.update', $weld) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected(old('object_id', $weld->object_id) == $object->id)>{{ $object->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="weld_number">Номер стыка</label>
                            <input id="weld_number" name="weld_number" value="{{ old('weld_number', $weld->weld_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="title_id">Титул</label>
                            <select id="title_id" name="title_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($titles as $title)
                                    <option value="{{ $title->id }}" @selected(old('title_id', $weld->title_id) == $title->id)>{{ $title->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="drawing_id">Чертеж</label>
                            <select id="drawing_id" name="drawing_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($drawings as $drawing)
                                    <option value="{{ $drawing->id }}" @selected(old('drawing_id', $weld->drawing_id) == $drawing->id)>{{ $drawing->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="line_id">Линия</label>
                            <select id="line_id" name="line_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($lines as $line)
                                    <option value="{{ $line->id }}" @selected(old('line_id', $weld->line_id) == $line->id)>{{ $line->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                            <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $weld->status->value) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить стык</button>
                        </div>
                    </form>
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
