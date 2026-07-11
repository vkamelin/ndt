@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Объекты / участки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Города</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Город является родительской сущностью для объектов/участков.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.cities.index') }}" class="grid gap-4 md:grid-cols-[1.5fr,1fr]">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить</button>
                </div>
            </form>
        </div>

        @can('cities.manage')
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Добавление города</h2>
                        <p class="mt-2 text-sm text-slate-600">Форма открывается в модальном окне.</p>
                    </div>
                    <button
                        type="button"
                        onclick="document.getElementById('city-create-dialog').showModal()"
                        class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700"
                    >
                        Добавить город
                    </button>
                </div>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Название</th>
                            <th class="px-6 py-4">Объектов</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Комментарий</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($cities as $city)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $city->name }}</p>
                                </td>
                                <td class="px-6 py-5 text-slate-700">{{ $city->objects_count }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $city->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $city->is_active ? 'Активно' : 'Неактивно' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-slate-700">{{ $city->comment ?: '—' }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        @can('cities.manage')
                                            <button
                                                type="button"
                                                onclick="document.getElementById('city-edit-{{ $city->id }}').showModal()"
                                                class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100"
                                            >
                                                Редактировать
                                            </button>
                                            <form method="post" action="{{ route('admin.cities.destroy', $city) }}">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">
                                                    Деактивировать
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $cities->links() }}

        @can('cities.manage')
            <dialog id="city-create-dialog" class="w-[min(42rem,calc(100vw-2rem))] rounded-3xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40">
                <form method="post" action="{{ route('admin.cities.store') }}" class="space-y-6 p-6">
                    @csrf
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-900">Добавить город</h2>
                            <p class="mt-2 text-sm text-slate-600">Небольшая форма редактируется в окне.</p>
                        </div>
                        <button type="button" onclick="this.closest('dialog').close()" aria-label="Закрыть" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            <span aria-hidden="true" class="text-2xl leading-none">&times;</span>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="city_create_name">Название города</label>
                        <input id="city_create_name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="city_create_comment">Комментарий</label>
                        <input id="city_create_comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', true))>
                        Активно
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить город</button>
                        <button type="button" onclick="this.closest('dialog').close()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Отмена</button>
                    </div>
                </form>
            </dialog>

            @foreach ($cities as $city)
                <dialog id="city-edit-{{ $city->id }}" class="w-[min(42rem,calc(100vw-2rem))] rounded-3xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40">
                    <form method="post" action="{{ route('admin.cities.update', $city) }}" class="space-y-6 p-6">
                        @csrf
                        @method('patch')
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-slate-900">Редактировать город</h2>
                                <p class="mt-2 text-sm text-slate-600">{{ $city->name }}</p>
                            </div>
                            <button type="button" onclick="this.closest('dialog').close()" aria-label="Закрыть" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                                <span aria-hidden="true" class="text-2xl leading-none">&times;</span>
                            </button>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="city_name_{{ $city->id }}">Название города</label>
                            <input id="city_name_{{ $city->id }}" name="name" value="{{ old('name', $city->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="city_comment_{{ $city->id }}">Комментарий</label>
                            <input id="city_comment_{{ $city->id }}" name="comment" value="{{ old('comment', $city->comment) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <label class="flex items-center gap-3 text-sm text-slate-600">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', $city->is_active))>
                            Активно
                        </label>
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить город</button>
                            <button type="button" onclick="this.closest('dialog').close()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Отмена</button>
                        </div>
                    </form>
                </dialog>
            @endforeach
        @endcan
    </div>
@endsection
