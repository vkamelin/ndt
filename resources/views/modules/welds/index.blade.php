@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Стыки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Стыки</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Список стыков и назначение методов контроля.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @can('welds.manage')
            <div class="panel p-6">
                <form method="post" action="{{ route('admin.welds.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                        <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="weld_number">Номер стыка</label>
                        <input id="weld_number" name="weld_number" value="{{ old('weld_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="title_id">Титул</label>
                        <select id="title_id" name="title_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Не выбран</option>
                            @foreach ($titles as $title)
                                <option value="{{ $title->id }}" @selected(old('title_id') == $title->id)>{{ $title->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="drawing_id">Чертеж</label>
                        <select id="drawing_id" name="drawing_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Не выбран</option>
                            @foreach ($drawings as $drawing)
                                <option value="{{ $drawing->id }}" @selected(old('drawing_id') == $drawing->id)>{{ $drawing->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="line_id">Линия</label>
                        <select id="line_id" name="line_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Не выбран</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->id }}" @selected(old('line_id') == $line->id)>{{ $line->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать стык</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Номер</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Методы</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($welds as $weld)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $weld->weld_number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $weld->title?->name ?: 'Без титула' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $weld->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $weld->object?->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    {{ $weld->ndtMethods->map(fn ($method) => $method->code->label())->join(', ') ?: 'Без методов' }}
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $weld->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.welds.show', $weld) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">Открыть</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $welds->links() }}
    </div>
@endsection
