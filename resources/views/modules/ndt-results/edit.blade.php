@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Результаты контроля</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $result->weld?->weld_number }} · Редактирование</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка редактируется на отдельной странице, чтобы не перегружать просмотр результата.
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
            <form method="post" action="{{ route('admin.ndt-results.update', $result) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                @method('patch')
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="executor_employee_id">Исполнитель</label>
                    <select id="executor_employee_id" name="executor_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('executor_employee_id', $result->executor_employee_id) == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="control_date">Дата контроля</label>
                    <input id="control_date" type="date" name="control_date" value="{{ old('control_date', optional($result->control_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="normative_document_id">НТД</label>
                    <select id="normative_document_id" name="normative_document_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($normativeDocuments as $document)
                            <option value="{{ $document->id }}" @selected(old('normative_document_id', $result->normative_document_id) == $document->id)>{{ $document->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="equipment_id">Оборудование</label>
                    <select id="equipment_id" name="equipment_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($equipment as $item)
                            <option value="{{ $item->id }}" @selected(old('equipment_id', $result->equipment_id) == $item->id)>{{ $item->name }} — {{ $item->inventory_number ?: $item->serial_number ?: $item->status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="result_text">Результат</label>
                    <textarea id="result_text" name="result_text" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('result_text', $result->result_text) }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment', $result->comment) }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить результат</button>
                    <a href="{{ route('admin.ndt-results.show', $result) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
