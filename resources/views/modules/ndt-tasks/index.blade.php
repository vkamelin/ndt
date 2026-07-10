@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Задания НК</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Задания НК</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Планирование контроля по методу, исполнителю и списку стыков.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.ndt-tasks.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
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
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="assignee_employee_id">Исполнитель</label>
                    <select id="assignee_employee_id" name="assignee_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Все исполнители</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(request('assignee_employee_id') == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="scope">Срез</label>
                    <select id="scope" name="scope" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Все задания</option>
                        <option value="mine" @selected(request('scope') === 'mine')>Мои задания</option>
                        <option value="overdue" @selected(request('scope') === 'overdue')>Просроченные</option>
                    </select>
                </div>
                <div class="xl:col-span-4">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить фильтры</button>
                </div>
            </form>
        </div>

        @can('create', \App\Modules\NdtTasks\Models\NdtTask::class)
            <div class="panel p-6">
                <form method="post" action="{{ route('admin.ndt-tasks.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="task_number">Номер задания</label>
                        <input id="task_number" name="task_number" value="{{ old('task_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="ndt_request_id">Заявка</label>
                        <select id="ndt_request_id" name="ndt_request_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($requests as $requestItem)
                                <option value="{{ $requestItem->id }}" @selected(old('ndt_request_id') == $requestItem->id)>{{ $requestItem->request_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="object_id_form">Объект/участок</label>
                        <select id="object_id_form" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="ndt_method_id_form">Метод контроля</label>
                        <select id="ndt_method_id_form" name="ndt_method_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($methods as $method)
                                <option value="{{ $method->id }}" @selected(old('ndt_method_id') == $method->id)>{{ $method->code->label() }} {{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="assignee_employee_id_form">Исполнитель</label>
                        <select id="assignee_employee_id_form" name="assignee_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Назначить позже</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('assignee_employee_id') == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="planned_date">Плановая дата</label>
                        <input id="planned_date" type="date" name="planned_date" value="{{ old('planned_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="priority">Приоритет</label>
                        <input id="priority" name="priority" value="{{ old('priority') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    </div>
                    <div class="md:col-span-2 xl:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment') }}</textarea>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="weld_ids">Стыки</label>
                        <select id="weld_ids" name="weld_ids[]" multiple class="min-h-48 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            @foreach ($welds as $weld)
                                <option value="{{ $weld->id }}" @selected(collect(old('weld_ids', []))->contains($weld->id))>
                                    {{ $weld->weld_number }} — {{ $weld->object?->name }} — {{ $weld->ndtMethods->map(fn ($method) => $method->code->label())->join(', ') ?: 'Без методов' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать задание</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Задание</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Метод</th>
                            <th class="px-6 py-4">Исполнитель</th>
                            <th class="px-6 py-4">Дата</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Стыков</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($tasks as $task)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $task->task_number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $task->request?->request_number ?: 'Без заявки' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $task->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $task->object?->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $task->method?->code?->label() }} {{ $task->method?->name }}</td>
                                <td class="px-6 py-5">{{ $task->assigneeEmployee?->fullName() ?: 'Не назначен' }}</td>
                                <td class="px-6 py-5">{{ $task->planned_date?->format('d.m.Y') }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $task->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">{{ $task->welds->count() }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.ndt-tasks.show', $task) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">Открыть</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $tasks->links() }}
    </div>
@endsection
