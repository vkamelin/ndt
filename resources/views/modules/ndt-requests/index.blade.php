@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заявки НК</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Заявки НК</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Входящий производственный контур: заявки, стыки и контроль статусов.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.ndt-requests.index') }}" class="grid gap-4 md:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Все статусы</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                    <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Все объекты</option>
                        @foreach ($objects as $object)
                            <option value="{{ $object->id }}" @selected(request('object_id') == $object->id)>{{ $object->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить фильтры</button>
                </div>
            </form>
        </div>

        @can('ndt_requests.manage')
            <div class="panel p-6">
                <form method="post" action="{{ route('admin.ndt-requests.store') }}" class="grid gap-4 md:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="request_number">Номер заявки</label>
                        <input id="request_number" name="request_number" value="{{ old('request_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="request_date">Дата заявки</label>
                        <input id="request_date" type="date" name="request_date" value="{{ old('request_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="organization_id">Заказчик</label>
                        <select id="organization_id" name="organization_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Не выбран</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->id }}" @selected(old('organization_id') == $organization->id)>{{ $organization->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="object_id_new">Объект/участок</label>
                        <select id="object_id_new" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                            @endforeach
                        </select>
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
                        <label class="text-sm font-medium text-slate-700" for="priority">Приоритет</label>
                        <input id="priority" name="priority" value="{{ old('priority') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="due_date">Срок выполнения</label>
                        <input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="basis">Основание работ</label>
                        <textarea id="basis" name="basis" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('basis') }}</textarea>
                    </div>
                    <div class="md:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment') }}</textarea>
                    </div>
                    <div class="md:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить заявку</button>
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
                            <th class="px-6 py-4">Организация</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Стыков</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($requests as $requestItem)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $requestItem->request_number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $requestItem->request_date?->format('d.m.Y') }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $requestItem->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $requestItem->object?->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $requestItem->organization?->name ?: '—' }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $requestItem->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">{{ $requestItem->welds->count() }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.ndt-requests.show', $requestItem) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $requests->links() }}
    </div>
@endsection
