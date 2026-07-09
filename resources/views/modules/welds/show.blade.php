@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Стыки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Стык {{ $weld->weld_number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка стыка с историей статусов и привязкой к сварщикам.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.5fr,1fr]">
            <div class="panel p-6">
                @can('welds.manage')
                    <form method="post" action="{{ route('admin.welds.update', $weld) }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected(old('object_id', $weld->object_id) == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
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
                            <label class="text-sm font-medium text-slate-700" for="diameter">Диаметр</label>
                            <input id="diameter" name="diameter" value="{{ old('diameter', $weld->diameter) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="thickness">Толщина</label>
                            <input id="thickness" name="thickness" value="{{ old('thickness', $weld->thickness) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="material_1_id">Материал 1</label>
                            <select id="material_1_id" name="material_1_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}" @selected(old('material_1_id', $weld->material_1_id) == $material->id)>{{ $material->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="material_2_id">Материал 2</label>
                            <select id="material_2_id" name="material_2_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}" @selected(old('material_2_id', $weld->material_2_id) == $material->id)>{{ $material->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="welded_at">Дата сварки</label>
                            <input id="welded_at" type="date" name="welded_at" value="{{ old('welded_at', optional($weld->welded_at)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить стык</button>
                        </div>
                    </form>
                @endcan
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Кратко</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Объект/участок</dt>
                        <dd class="font-medium text-slate-900">{{ $weld->object?->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Город</dt>
                        <dd class="font-medium text-slate-900">{{ $weld->object?->city?->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Статус</dt>
                        <dd class="font-medium text-slate-900">{{ $weld->status->label() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Сварщиков</dt>
                        <dd class="font-medium text-slate-900">{{ $weld->welders->count() }}</dd>
                    </div>
                </dl>

                @can('welds.manage')
                    <form method="post" action="{{ route('admin.welds.status.update', $weld) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                            <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach (\App\Modules\Welds\Enums\WeldStatus::options() as $value => $label)
                                    <option value="{{ $value }}" @selected($weld->status->value === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status_comment">Комментарий</label>
                            <input id="status_comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Обновить статус</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr,1fr]">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Сварщики</h2>
                @can('welds.manage')
                    <form method="post" action="{{ route('admin.welds.welders.attach', $weld) }}" class="flex flex-wrap gap-3">
                        @csrf
                        <select name="welder_id" class="min-w-72 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($welders as $welder)
                                <option value="{{ $welder->id }}">{{ $welder->displayName() }} / {{ $welder->stamp }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить сварщика</button>
                    </form>
                @endcan

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Имя</th>
                                <th class="px-6 py-4">Клеймо</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($weld->welders as $welder)
                                <tr>
                                    <td class="px-6 py-5">{{ $welder->displayName() }}</td>
                                    <td class="px-6 py-5">{{ $welder->stamp }}</td>
                                    <td class="px-6 py-5">
                                        @can('welds.manage')
                                            <form method="post" action="{{ route('admin.welds.welders.detach', [$weld, $welder]) }}">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Удалить</button>
                                            </form>
                                        @else
                                            <span class="text-slate-500">Только просмотр</span>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
    </div>
@endsection
