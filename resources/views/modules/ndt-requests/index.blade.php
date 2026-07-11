@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="panel-title">Заявки НК</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900">Заявки НК</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                        Список заявок с фильтрами. Создание и импорт вынесены на отдельные страницы.
                    </p>
                </div>

                @can('ndt_requests.manage')
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.ndt-requests.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">
                            Создать заявку
                        </a>
                    </div>
                @endcan
            </div>
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
                            <option value="{{ $object->id }}" @selected(request('object_id') == $object->id)>{{ $object->name }} @if($object->city)({{ $object->city->name }})@endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить фильтры</button>
                </div>
            </form>
        </div>

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Номер</th>
                            <th class="px-6 py-4">Дата</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Заказчик</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Стыков</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($requests as $requestItem)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $requestItem->request_number }}</p>
                                </td>
                                <td class="px-6 py-5 text-slate-600">{{ $requestItem->request_date?->format('d.m.Y') }}</td>
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
                                <td class="px-6 py-5">{{ $requestItem->welds_count }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.ndt-requests.show', $requestItem) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">
                                    Заявок пока нет.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $requests->links() }}
    </div>
@endsection
