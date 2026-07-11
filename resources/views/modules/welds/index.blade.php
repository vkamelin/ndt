@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Стыки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Стыки</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Список стыков и назначение методов контроля.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @can('create', \App\Modules\Welds\Models\Weld::class)
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Создание стыка</h2>
                        <p class="mt-2 text-sm text-slate-600">Большая форма перенесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.welds.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать стык</a>
                </div>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Номер</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Методы</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($welds as $weld)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $weld->weld_number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $weld->title?->name ?: 'Без титула' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $weld->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $weld->object?->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    {{ $weld->ndtMethods->map(fn ($method) => $method->code->label())->join(', ') ?: 'Без методов' }}
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $weld->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.welds.show', $weld) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">Открыть</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $welds->links() }}
    </div>
@endsection
