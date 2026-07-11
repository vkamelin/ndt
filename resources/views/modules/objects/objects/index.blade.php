@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Объекты / участки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Объекты / участки</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Объект/участок относится к городу и является областью ответственности сотрудников.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @can('objects.manage')
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Добавление объекта</h2>
                        <p class="mt-2 text-sm text-slate-600">Форма открывается в модальном окне.</p>
                    </div>
                    <button
                        type="button"
                        onclick="document.getElementById('object-create-dialog').showModal()"
                        class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700"
                    >
                        Добавить объект
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
                            <th class="px-6 py-4">Город</th>
                            <th class="px-6 py-4">Заказчик</th>
                            <th class="px-6 py-4">Код</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($objects as $object)
                            <tr class="align-top">
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $object->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $object->comment ?: 'Без комментария' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $object->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $object->organization?->name ?: 'Не выбран' }}</td>
                                <td class="px-6 py-5">{{ $object->code ?: '—' }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $object->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $object->is_active ? 'Активно' : 'Неактивно' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        @can('objects.manage')
                                            <button
                                                type="button"
                                                onclick="document.getElementById('object-edit-{{ $object->id }}').showModal()"
                                                class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100"
                                            >
                                                Редактировать
                                            </button>
                                            <form method="post" action="{{ route('admin.objects.destroy', $object) }}">
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

        {{ $objects->links() }}

        @can('objects.manage')
            <dialog id="object-create-dialog" class="w-[min(46rem,calc(100vw-2rem))] rounded-3xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40">
                <form method="post" action="{{ route('admin.objects.store') }}" class="space-y-6 p-6">
                    @csrf
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-slate-900">Добавить объект</h2>
                            <p class="mt-2 text-sm text-slate-600">Небольшая форма открывается в окне.</p>
                        </div>
                        <button type="button" onclick="this.closest('dialog').close()" aria-label="Закрыть" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            <span aria-hidden="true" class="text-2xl leading-none">&times;</span>
                        </button>
                    </div>
                    <div class="grid gap-4 md:grid-cols-[1fr,1fr,1fr]">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_create_city_id">Город</label>
                            <select id="object_create_city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @selected(old('city_id') == $city->id)>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_create_name">Название объекта/участка</label>
                            <input id="object_create_name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_create_organization_id">Заказчик</label>
                            <select id="object_create_organization_id" name="organization_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Не выбран</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}" @selected(old('organization_id') == $organization->id)>{{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_create_code">Код</label>
                            <input id="object_create_code" name="code" value="{{ old('code') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="md:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_create_comment">Комментарий</label>
                            <input id="object_create_comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-3">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', true))>
                            Активно
                        </label>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить объект</button>
                        <button type="button" onclick="this.closest('dialog').close()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Отмена</button>
                    </div>
                </form>
            </dialog>

            @foreach ($objects as $object)
                <dialog id="object-edit-{{ $object->id }}" class="w-[min(46rem,calc(100vw-2rem))] rounded-3xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/40">
                    <form method="post" action="{{ route('admin.objects.update', $object) }}" class="space-y-6 p-6">
                        @csrf
                        @method('patch')
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-slate-900">Редактировать объект</h2>
                                <p class="mt-2 text-sm text-slate-600">{{ $object->name }}</p>
                            </div>
                            <button type="button" onclick="this.closest('dialog').close()" aria-label="Закрыть" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                                <span aria-hidden="true" class="text-2xl leading-none">&times;</span>
                            </button>
                        </div>
                        <div class="grid gap-4 md:grid-cols-[1fr,1fr,1fr]">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-700" for="object_city_{{ $object->id }}">Город</label>
                                <select id="object_city_{{ $object->id }}" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}" @selected(old('city_id', $object->city_id) == $city->id)>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-700" for="object_name_{{ $object->id }}">Название объекта/участка</label>
                                <input id="object_name_{{ $object->id }}" name="name" value="{{ old('name', $object->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-700" for="object_org_{{ $object->id }}">Заказчик</label>
                                <select id="object_org_{{ $object->id }}" name="organization_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                    <option value="">Не выбран</option>
                                    @foreach ($organizations as $organization)
                                        <option value="{{ $organization->id }}" @selected(old('organization_id', $object->organization_id) == $organization->id)>{{ $organization->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-700" for="object_code_{{ $object->id }}">Код</label>
                                <input id="object_code_{{ $object->id }}" name="code" value="{{ old('code', $object->code) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            </div>
                            <div class="md:col-span-3 space-y-2">
                                <label class="text-sm font-medium text-slate-700" for="object_comment_{{ $object->id }}">Комментарий</label>
                                <input id="object_comment_{{ $object->id }}" name="comment" value="{{ old('comment', $object->comment) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            </div>
                            <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-3">
                                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', $object->is_active))>
                                Активно
                            </label>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить объект</button>
                            <button type="button" onclick="this.closest('dialog').close()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">Отмена</button>
                        </div>
                    </form>
                </dialog>
            @endforeach
        @endcan
    </div>
@endsection
