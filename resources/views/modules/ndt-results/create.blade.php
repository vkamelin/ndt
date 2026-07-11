@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Результаты контроля</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Создание результата</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма создания вынесена с общего списка.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <p class="font-semibold">Проверьте форму:</p>
                <ul class="mt-2 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="panel p-6">
            <form method="post" action="{{ route('admin.ndt-results.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="ndt_task_id">Задание</label>
                    <select id="ndt_task_id" name="ndt_task_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($tasks as $task)
                            <option value="{{ $task->id }}" @selected(old('ndt_task_id') == $task->id)>{{ $task->task_number }} — {{ $task->method?->code?->label() }} — {{ $task->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="weld_id">Стык</label>
                    <select id="weld_id" name="weld_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($welds as $weld)
                            <option value="{{ $weld->id }}" @selected(old('weld_id') == $weld->id)>{{ $weld->weld_number }} — {{ $weld->object?->name }} — {{ $weld->ndtMethods->map(fn ($method) => $method->code->label())->join(', ') ?: 'Без методов' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="executor_employee_id">Исполнитель</label>
                    <select id="executor_employee_id" name="executor_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">По заданию</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('executor_employee_id') == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="control_date">Дата контроля</label>
                    <input id="control_date" type="date" name="control_date" value="{{ old('control_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="normative_document_id">НТД</label>
                    <select id="normative_document_id" name="normative_document_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($normativeDocuments as $document)
                            <option value="{{ $document->id }}" @selected(old('normative_document_id') == $document->id)>{{ $document->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="equipment_id">Оборудование</label>
                    <input id="equipment_id" name="equipment_id" value="{{ old('equipment_id') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="result_text">Результат</label>
                    <textarea id="result_text" name="result_text" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('result_text') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать результат</button>
                    <a href="{{ route('admin.ndt-results.index') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
