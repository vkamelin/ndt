@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Радиографический контроль</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Создание карты РК</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма создания перенесена со списка.
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
            <form method="post" action="{{ route('admin.radiography.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="ndt_result_id">Общий результат</label>
                    <select id="ndt_result_id" name="ndt_result_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($ndtResults as $ndtResult)
                            <option value="{{ $ndtResult->id }}" @selected(old('ndt_result_id') == $ndtResult->id)>{{ $ndtResult->weld?->weld_number }} — {{ $ndtResult->weld?->object?->name }} — {{ $ndtResult->method?->code?->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="film_type_id">Тип пленки</label>
                    <select id="film_type_id" name="film_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($filmTypes as $filmType)
                            <option value="{{ $filmType->id }}" @selected(old('film_type_id') == $filmType->id)>{{ $filmType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="barcode">Баркод</label>
                    <input id="barcode" name="barcode" value="{{ old('barcode') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="conclusion_number">Номер заключения</label>
                    <input id="conclusion_number" name="conclusion_number" value="{{ old('conclusion_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="control_date">Дата контроля</label>
                    <input id="control_date" type="date" name="control_date" value="{{ old('control_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="conclusion_date">Дата заключения</label>
                    <input id="conclusion_date" type="date" name="conclusion_date" value="{{ old('conclusion_date') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="archive_location">Архивное место</label>
                    <input id="archive_location" name="archive_location" value="{{ old('archive_location') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="result_text">Итог</label>
                    <textarea id="result_text" name="result_text" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('result_text') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить карту РК</button>
                    <a href="{{ route('admin.radiography.index') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
