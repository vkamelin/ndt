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
            <form method="post" action="{{ route('admin.cities.store') }}" class="grid gap-4 md:grid-cols-[1.5fr,1fr]">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="name">Название города</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    @error('name')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <input id="comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-2">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', true))>
                    Активно
                </label>
                <div class="md:col-span-2">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить город</button>
                </div>
            </form>
        </div>

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
                                    <form method="post" action="{{ route('admin.cities.update', $city) }}" class="space-y-3">
                                        @csrf
                                        @method('patch')
                                        <input name="name" value="{{ old('name', $city->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                </td>
                                <td class="px-6 py-5 text-slate-700">{{ $city->objects_count }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $city->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $city->is_active ? 'Активно' : 'Неактивно' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <textarea name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment', $city->comment) }}</textarea>
                                    <label class="mt-3 flex items-center gap-3 text-xs font-medium text-slate-500">
                                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', $city->is_active))>
                                        Активно
                                    </label>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.cities.destroy', $city) }}">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">
                                            Деактивировать
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $cities->links() }}
    </div>
@endsection
