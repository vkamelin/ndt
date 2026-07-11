@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Стыки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $weld->weld_number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Отдельная форма редактирования стыка.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <p class="font-semibold">Проверьте форму:</p>
                <ul class="mt-2 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="panel p-6">
            <form method="post" action="{{ route('admin.welds.update', $weld) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                @method('patch')
                @if ($isAdmin)
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                        <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}" @selected(old('object_id', $weld->object_id) == $object->id)>{{ $object->name }} @if($object->city)({{ $object->city->name }})@endif</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="object_id" value="{{ $currentObject?->id }}">
                    <div class="md:col-span-2 xl:col-span-3 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Контекст объекта</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $currentObject?->name }}</p>
                        <p class="mt-1 text-slate-500">{{ $currentObject?->city?->name }}</p>
                    </div>
                @endif
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
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить стык</button>
                    <a href="{{ route('admin.welds.show', $weld) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
