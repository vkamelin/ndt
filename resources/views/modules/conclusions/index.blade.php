@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заключения</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Официальные заключения по результатам контроля</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Здесь формируются проекты заключений, выполняется проверка, утверждение, выдача и выпуск новых версий.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.conclusions.index') }}" class="grid gap-4 md:grid-cols-4">
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected((string) request('status') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="ndt_method_id">Метод</label>
                    <select id="ndt_method_id" name="ndt_method_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($methods as $method)
                            <option value="{{ $method->id }}" @selected((string) request('ndt_method_id') === (string) $method->id)>{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                    <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($objects as $object)
                            <option value="{{ $object->id }}" @selected((string) request('object_id') === (string) $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить</button>
                </div>
            </form>
        </div>

        @can('create', \App\Modules\Conclusions\Models\Conclusion::class)
            <div class="panel p-6">
                <form method="post" action="{{ route('admin.conclusions.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                        <input id="number" name="number" value="{{ old('number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="date">Дата</label>
                        <input id="date" type="date" name="date" value="{{ old('date', today()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2 xl:col-span-3">
                        <label class="text-sm font-medium text-slate-700" for="result_ids">Результаты, готовые к заключению</label>
                        <select id="result_ids" name="result_ids[]" multiple size="8" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($readyResults as $result)
                                <option value="{{ $result->id }}" @selected(collect(old('result_ids', []))->contains($result->id))>
                                    {{ $result->weld?->weld_number }} | {{ $result->method?->name }} | {{ $result->weld?->object?->name }} | {{ $result->control_date?->format('d.m.Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xl:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                    </div>
                    <div class="xl:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать заключение</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Заключение</th>
                            <th class="px-6 py-4">Связь</th>
                            <th class="px-6 py-4">Позиции</th>
                            <th class="px-6 py-4">Версии</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($conclusions as $conclusion)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $conclusion->number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $conclusion->date?->format('d.m.Y') }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $conclusion->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $conclusion->method?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $conclusion->request?->request_number ?: 'Без заявки' }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $conclusion->items_count }}</td>
                                <td class="px-6 py-5">{{ $conclusion->versions_count }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $conclusion->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.conclusions.show', $conclusion) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Заключения не найдены.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $conclusions->links() }}
    </div>
@endsection
