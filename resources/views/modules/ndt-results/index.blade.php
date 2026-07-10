@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Результаты контроля</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Результаты контроля</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Общий результат контроля и формы ВИК, ПВК, МК, УК.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.ndt-results.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск по стыку</label>
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
                            <option value="{{ $object->id }}" @selected(request('object_id') == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="ndt_method_id">Метод контроля</label>
                    <select id="ndt_method_id" name="ndt_method_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Все методы</option>
                        @foreach ($methods as $method)
                            <option value="{{ $method->id }}" @selected(request('ndt_method_id') == $method->id)>{{ $method->code->label() }} {{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="xl:col-span-4">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить фильтры</button>
                </div>
            </form>
        </div>

        @can('ndt_results.manage')
            <div class="panel p-6">
                <form method="post" action="{{ route('admin.ndt-results.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="ndt_task_id">Задание</label>
                        <select id="ndt_task_id" name="ndt_task_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($tasks as $task)
                                <option value="{{ $task->id }}" @selected(old('ndt_task_id') == $task->id)>{{ $task->task_number }} — {{ $task->method?->code?->label() }} — {{ $task->object?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="weld_id">Стык</label>
                        <select id="weld_id" name="weld_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($welds as $weld)
                                <option value="{{ $weld->id }}" @selected(old('weld_id') == $weld->id)>{{ $weld->weld_number }} — {{ $weld->object?->name }} — {{ $weld->ndtMethods->map(fn ($method) => $method->code->label())->join(', ') ?: 'Без методов' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="executor_employee_id">Исполнитель</label>
                        <select id="executor_employee_id" name="executor_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">По заданию</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('executor_employee_id') == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="control_date">Дата контроля</label>
                        <input id="control_date" type="date" name="control_date" value="{{ old('control_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="normative_document_id">НТД</label>
                        <select id="normative_document_id" name="normative_document_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Не указано</option>
                            @foreach ($normativeDocuments as $document)
                                <option value="{{ $document->id }}" @selected(old('normative_document_id') == $document->id)>{{ $document->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="equipment_id">Оборудование</label>
                        <input id="equipment_id" name="equipment_id" value="{{ old('equipment_id') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="md:col-span-2 xl:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="result_text">Результат</label>
                        <textarea id="result_text" name="result_text" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('result_text') }}</textarea>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment') }}</textarea>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать результат</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Стык</th>
                            <th class="px-6 py-4">Метод</th>
                            <th class="px-6 py-4">Исполнитель</th>
                            <th class="px-6 py-4">Дата</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Дефекты</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($results as $result)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $result->weld?->weld_number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $result->weld?->object?->city?->name }} · {{ $result->weld?->object?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $result->method?->code?->label() }} {{ $result->method?->name }}</td>
                                <td class="px-6 py-5">{{ $result->executorEmployee?->fullName() }}</td>
                                <td class="px-6 py-5">{{ $result->control_date?->format('d.m.Y') }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $result->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">{{ $result->defects->count() }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.ndt-results.show', $result) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">Открыть</a>
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
