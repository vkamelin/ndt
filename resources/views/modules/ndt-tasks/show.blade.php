@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Задания НК</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $task->task_number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка задания, список стыков и переходы статуса.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.5fr,1fr]">
            <div class="panel p-6 space-y-4">
                @can('update', $task)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">Редактирование задания</p>
                                <p class="mt-1 text-sm text-slate-600">Основная форма перенесена на отдельную страницу.</p>
                            </div>
                            <a href="{{ route('admin.ndt-tasks.edit', $task) }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Редактировать задание</a>
                        </div>
                    </div>
                @endcan

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Объект/участок</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $task->object?->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $task->object?->city?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Метод и исполнитель</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $task->method?->code?->label() }} {{ $task->method?->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $task->assigneeEmployee?->fullName() ?: 'Не назначен' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Заявка</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $task->request?->request_number ?: 'Без заявки' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $task->status->label() }}</p>
                    </div>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Действия</h2>

                @php($statusComment = old('comment'))
                @can('accept', $task)
                    <form method="post" action="{{ route('admin.ndt-tasks.status.accept', $task) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Принять задание</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Принять</button>
                    </form>
                @endcan

                @can('startWork', $task)
                    <form method="post" action="{{ route('admin.ndt-tasks.status.start', $task) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Начать работу</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">В работу</button>
                    </form>
                @endcan

                @can('complete', $task)
                    <form method="post" action="{{ route('admin.ndt-tasks.status.complete', $task) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Завершить задание</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Выполнено</button>
                    </form>
                @endcan

                @can('completePartial', $task)
                    <form method="post" action="{{ route('admin.ndt-tasks.status.partial', $task) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Отметить частичное выполнение</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Частично выполнено</button>
                    </form>
                @endcan

                @can('returnTask', $task)
                    <form method="post" action="{{ route('admin.ndt-tasks.status.return', $task) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Вернуть задание</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full border border-amber-200 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-50">Вернуть</button>
                    </form>
                @endcan

                @can('cancel', $task)
                    <form method="post" action="{{ route('admin.ndt-tasks.status.cancel', $task) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Отменить задание</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Отменить</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr,1fr]">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Стыки задания</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Позиция</th>
                                <th class="px-6 py-4">Стык</th>
                                <th class="px-6 py-4">Объект</th>
                                <th class="px-6 py-4">Методы</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($task->items as $item)
                                <tr>
                                    <td class="px-6 py-5">{{ $item->position_number }}</td>
                                    <td class="px-6 py-5">{{ $item->weld?->weld_number }}</td>
                                    <td class="px-6 py-5">{{ $item->weld?->object?->name }}</td>
                                    <td class="px-6 py-5">{{ $item->weld?->ndtMethods->map(fn ($method) => $method->code->label())->join(', ') ?: 'Без методов' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">История статусов</h2>
                <div class="space-y-3">
                    @foreach ($task->statusHistory as $history)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="font-medium text-slate-900">{{ $history->from_status ?: '—' }} → {{ $history->to_status }}</p>
                                <p class="text-slate-500">{{ $history->created_at?->format('d.m.Y H:i') }}</p>
                            </div>
                            <p class="mt-2 text-slate-600">
                                {{ $history->changedBy?->name ?: 'Система' }}@if($history->comment) · {{ $history->comment }}@endif
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
