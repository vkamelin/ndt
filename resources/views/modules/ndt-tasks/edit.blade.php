@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Задания НК</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $task->task_number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Отдельная форма редактирования задания.
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
            <form method="post" action="{{ route('admin.ndt-tasks.update', $task) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                @method('patch')
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="task_number">Номер задания</label>
                    <input id="task_number" name="task_number" value="{{ old('task_number', $task->task_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="ndt_request_id">Заявка</label>
                    <select id="ndt_request_id" name="ndt_request_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        @foreach ($requests as $requestItem)
                            <option value="{{ $requestItem->id }}" @selected(old('ndt_request_id', $task->ndt_request_id) == $requestItem->id)>{{ $requestItem->request_number }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($isAdmin)
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                        <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}" @selected(old('object_id', $task->object_id) == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="object_id" value="{{ $currentObject?->id }}">
                    <div class="md:col-span-2 xl:col-span-3 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Контекст объекта</p>
                        <p class="mt-1 font-medium text-slate-900">{{ $currentObject?->name }}</p>
                        <p class="mt-1 text-slate-500">{{ $currentObject?->city?->name }}</p>
                    </div>
                @endif
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="ndt_method_id">Метод контроля</label>
                    <select id="ndt_method_id" name="ndt_method_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        @foreach ($methods as $method)
                            <option value="{{ $method->id }}" @selected(old('ndt_method_id', $task->ndt_method_id) == $method->id)>{{ $method->code->label() }} {{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="assignee_employee_id">Исполнитель</label>
                    <select id="assignee_employee_id" name="assignee_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Не назначен</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('assignee_employee_id', $task->assignee_employee_id) == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="planned_date">Плановая дата</label>
                    <input id="planned_date" type="date" name="planned_date" value="{{ old('planned_date', optional($task->planned_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="priority">Приоритет</label>
                    <input id="priority" name="priority" value="{{ old('priority', $task->priority) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment', $task->comment) }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="weld_ids">Стыки</label>
                    <select id="weld_ids" name="weld_ids[]" multiple class="min-h-48 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        @php($selectedWeldIds = old('weld_ids', $task->welds->pluck('id')->all()))
                        @foreach ($welds as $weld)
                            <option value="{{ $weld->id }}" @selected(collect($selectedWeldIds)->contains($weld->id))>
                                {{ $weld->weld_number }} — {{ $weld->object?->name }} — {{ $weld->ndtMethods->map(fn ($method) => $method->code->label())->join(', ') ?: 'Без методов' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить задание</button>
                    <a href="{{ route('admin.ndt-tasks.show', $task) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
