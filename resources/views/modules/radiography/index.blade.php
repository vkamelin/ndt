@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Радиографический контроль</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">РК, пленки, снимки и пересветы</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Раздел для карточек РК, пленок, снимков, плотностей, пересветов и архивных позиций.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.radiography.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все статусы</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Применить</button>
                </div>
            </form>
        </div>

        @can('radiography.manage')
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Создание карты РК</h2>
                        <p class="mt-2 text-sm text-slate-600">Большая форма перенесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.radiography.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать карту РК</a>
                </div>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Стык</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Пленки</th>
                            <th class="px-6 py-4">Плотности</th>
                            <th class="px-6 py-4">Архив</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($results as $result)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $result->ndtResult?->weld?->weld_number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $result->ndtResult?->weld?->object?->city?->name }} · {{ $result->ndtResult?->weld?->object?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $result->status->label() }}</td>
                                <td class="px-6 py-5">{{ $result->films->count() }}</td>
                                <td class="px-6 py-5">{{ $result->densityMeasurements->count() }}</td>
                                <td class="px-6 py-5">{{ $result->archiveItems->count() }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.radiography.show', $result) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700">Открыть</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $results->links() }}
    </div>
@endsection
