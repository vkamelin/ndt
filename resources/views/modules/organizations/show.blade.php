@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Организации</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $organization->name }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка организации, контактов и лабораторий.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            @can('organizations.manage')
                <form method="post" action="{{ route('admin.organizations.update', $organization) }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    @method('patch')
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-slate-700" for="name">Наименование организации</label>
                        <input id="name" name="name" value="{{ old('name', $organization->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment', $organization->comment) }}</textarea>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-2">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', $organization->is_active))>
                        Активно
                    </label>
                    <div class="md:col-span-2">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить организацию</button>
                    </div>
                </form>
            @else
                <p class="text-sm text-slate-600">Просмотр карточки доступен без права управления, редактирование скрыто.</p>
            @endcan
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel p-6 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Контакты</h2>
                        <p class="mt-2 text-sm text-slate-600">Контактные лица организации.</p>
                    </div>
                </div>

                @can('organizations.manage')
                    <form method="post" action="{{ route('admin.organizations.contacts.store', $organization) }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="contact_name">Имя контакта</label>
                            <input id="contact_name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="contact_position">Должность</label>
                            <input id="contact_position" name="position" value="{{ old('position') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="contact_phone">Телефон</label>
                            <input id="contact_phone" name="phone" value="{{ old('phone') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="contact_email">Email</label>
                            <input id="contact_email" name="email" type="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="contact_comment">Комментарий</label>
                            <input id="contact_comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-2">
                            <input type="checkbox" name="is_primary" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_primary'))>
                            Основной контакт
                        </label>
                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить контакт</button>
                        </div>
                    </form>
                @endcan

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Имя</th>
                                <th class="px-6 py-4">Данные</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($organization->contacts as $contact)
                                <tr>
                                    <td class="px-6 py-5">
                                        <form method="post" action="{{ route('admin.organizations.contacts.update', [$organization, $contact]) }}" class="space-y-3">
                                            @csrf
                                            @method('patch')
                                            <input name="name" value="{{ old('name', $contact->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="space-y-3">
                                            <input name="position" value="{{ old('position', $contact->position) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            <input name="phone" value="{{ old('phone', $contact->phone) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            <input name="email" type="email" value="{{ old('email', $contact->email) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            <input name="comment" value="{{ old('comment', $contact->comment) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            <label class="flex items-center gap-3 text-xs font-medium text-slate-500">
                                                <input type="checkbox" name="is_primary" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_primary', $contact->is_primary))>
                                                Основной
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        @can('organizations.manage')
                                            <div class="flex flex-wrap gap-2">
                                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить</button>
                                        </form>
                                                <form method="post" action="{{ route('admin.organizations.contacts.destroy', [$organization, $contact]) }}">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Удалить</button>
                                                </form>
                                            </div>
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
                <div>
                    <h2 class="text-2xl font-semibold text-slate-900">Лаборатории</h2>
                    <p class="mt-2 text-sm text-slate-600">Лабораторные профили, связанные с организацией.</p>
                </div>

                @can('organizations.manage')
                    <form method="post" action="{{ route('admin.organizations.laboratories.store', $organization) }}" class="grid gap-4 md:grid-cols-2">
                        @csrf
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="laboratory_name">Наименование лаборатории</label>
                            <input id="laboratory_name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="laboratory_comment">Комментарий</label>
                            <input id="laboratory_comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-2">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', true))>
                            Активно
                        </label>
                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить лабораторию</button>
                        </div>
                    </form>
                @endcan

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Наименование</th>
                                <th class="px-6 py-4">Статус</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($organization->laboratories as $laboratory)
                                <tr>
                                    <td class="px-6 py-5">
                                        <form method="post" action="{{ route('admin.organizations.laboratories.update', [$organization, $laboratory]) }}" class="space-y-3">
                                            @csrf
                                            @method('patch')
                                            <input name="name" value="{{ old('name', $laboratory->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            <input name="comment" value="{{ old('comment', $laboratory->comment) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $laboratory->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $laboratory->is_active ? 'Активно' : 'Неактивно' }}
                                        </span>
                                        <label class="mt-3 flex items-center gap-3 text-xs font-medium text-slate-500">
                                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', $laboratory->is_active))>
                                            Активно
                                        </label>
                                    </td>
                                    <td class="px-6 py-5">
                                        @can('organizations.manage')
                                            <div class="flex flex-wrap gap-2">
                                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить</button>
                                        </form>
                                                <form method="post" action="{{ route('admin.organizations.laboratories.destroy', [$organization, $laboratory]) }}">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Удалить</button>
                                                </form>
                                            </div>
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
        </div>
    </div>
@endsection
