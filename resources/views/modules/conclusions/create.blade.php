@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заключения</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Создание заключения</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма создания вынесена отдельно от списка.
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
            <form method="post" action="{{ route('admin.conclusions.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                    <input id="number" name="number" value="{{ old('number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="date">Дата</label>
                    <input id="date" type="date" name="date" value="{{ old('date', today()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2 xl:col-span-3">
                    <label class="text-sm font-medium text-slate-700" for="result_ids">Результаты, готовые к заключению</label>
                    <select id="result_ids" name="result_ids[]" multiple size="8" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($readyResults as $result)
                            <option value="{{ $result->id }}" @selected(collect(old('result_ids', []))->contains($result->id))>
                                {{ $result->weld?->weld_number }} | {{ $result->method?->name }} | {{ $result->weld?->object?->name }} | {{ $result->control_date?->format('d.m.Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                </div>
                <div class="xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать заключение</button>
                    <a href="{{ route('admin.conclusions.index') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
