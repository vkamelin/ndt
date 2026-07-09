@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заявки НК</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $request->request_number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка заявки, список стыков и история статусов.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.5fr,1fr]">
            <div class="panel p-6">
                @can('ndt_requests.manage')
                    <form method="post" action="{{ route('admin.ndt-requests.update', $request) }}" class="grid gap-4 md:grid-cols-3">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="request_number">Номер заявки</label>
                            <input id="request_number" name="request_number" value="{{ old('request_number', $request->request_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="request_date">Дата заявки</label>
                            <input id="request_date" type="date" name="request_date" value="{{ old('request_date', optional($request->request_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="organization_id">Заказчик</label>
                            <select id="organization_id" name="organization_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}" @selected(old('organization_id', $request->organization_id) == $organization->id)>{{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected(old('object_id', $request->object_id) == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="title_id">Титул</label>
                            <select id="title_id" name="title_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($titles as $title)
                                    <option value="{{ $title->id }}" @selected(old('title_id', $request->title_id) == $title->id)>{{ $title->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="priority">Приоритет</label>
                            <input id="priority" name="priority" value="{{ old('priority', $request->priority) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="due_date">Срок выполнения</label>
                            <input id="due_date" type="date" name="due_date" value="{{ old('due_date', optional($request->due_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="basis">Основание работ</label>
                            <textarea id="basis" name="basis" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('basis', $request->basis) }}</textarea>
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment', $request->comment) }}</textarea>
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить заявку</button>
                        </div>
                    </form>
                @endcan
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Кратко</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Объект/участок</dt>
                        <dd class="font-medium text-slate-900">{{ $request->object?->name }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Организация</dt>
                        <dd class="font-medium text-slate-900">{{ $request->organization?->name ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Статус</dt>
                        <dd class="font-medium text-slate-900">{{ $request->status->label() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-slate-500">Стыков</dt>
                        <dd class="font-medium text-slate-900">{{ $request->welds->count() }}</dd>
                    </div>
                </dl>

                @can('ndt_requests.manage')
                    <form method="post" action="{{ route('admin.ndt-requests.status.update', $request) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                            <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($request->status->value === $value)>{{ $label }}</option>
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
                <h2 class="text-2xl font-semibold text-slate-900">Стыки заявки</h2>

                @can('ndt_requests.manage')
                    <form method="post" action="{{ route('admin.ndt-requests.welds.attach', $request) }}" class="flex flex-wrap gap-3">
                        @csrf
                        <select name="weld_id" class="min-w-72 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($welds as $weld)
                                <option value="{{ $weld->id }}">{{ $weld->weld_number }} / {{ $weld->object?->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить стык</button>
                    </form>
                @endcan

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Номер</th>
                                <th class="px-6 py-4">Объект</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($request->welds as $weld)
                                <tr>
                                    <td class="px-6 py-5">{{ $weld->weld_number }}</td>
                                    <td class="px-6 py-5">{{ $weld->object?->name }}</td>
                                    <td class="px-6 py-5">
                                        @can('ndt_requests.manage')
                                            <form method="post" action="{{ route('admin.ndt-requests.welds.detach', [$request, $weld]) }}">
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
                    @foreach ($request->statusHistory as $history)
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
