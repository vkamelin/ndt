@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Организации</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Организации</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Организация используется как заказчик, подрядчик или лаборатория в контуре заявок и стыков.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.organizations.index') }}" class="grid gap-4 md:grid-cols-[1fr,auto]">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить</button>
                </div>
            </form>
        </div>

        @can('organizations.manage')
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Создание организации</h2>
                        <p class="mt-2 text-sm text-slate-600">Форма вынесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.organizations.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить организацию</a>
                </div>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Организация</th>
                            <th class="px-6 py-4">Контакты</th>
                            <th class="px-6 py-4">Лаборатории</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($organizations as $organization)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $organization->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $organization->comment ?: 'Без комментария' }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $organization->contacts_count }}</td>
                                <td class="px-6 py-5">{{ $organization->laboratories_count }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $organization->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $organization->is_active ? 'Активно' : 'Неактивно' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.organizations.show', $organization) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                            Открыть
                                        </a>
                                        @can('organizations.manage')
                                            <form method="post" action="{{ route('admin.organizations.destroy', $organization) }}">
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

        {{ $organizations->links() }}
    </div>
@endsection
