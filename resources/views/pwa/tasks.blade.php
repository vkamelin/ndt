@extends('layouts.pwa')

@section('title', 'Мои задания НК')

@section('content')
    <div class="grid gap-4 lg:grid-cols-[1.5fr_0.8fr]">
        <section class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/25">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Назначенные задания</h2>
                    <p class="text-sm text-slate-300">Короткий список задач, доступный с телефона или планшета.</p>
                </div>
                <span class="rounded-full bg-sky-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-sky-200">{{ $tasks->total() }}</span>
            </div>

            <div class="space-y-3">
                @forelse ($tasks as $task)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold text-white">{{ $task->task_number }}</h3>
                                    <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs text-slate-200">{{ $task->status->label() }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-300">
                                    Стык: {{ $task->items->count() }} · Метод: {{ $task->method?->name ?? 'не указан' }} · Срок: {{ $task->planned_date?->format('d.m.Y') ?? 'не задан' }}
                                </p>
                            </div>
                            <a href="{{ route('admin.ndt-tasks.show', $task) }}" class="rounded-full border border-sky-400/30 px-4 py-2 text-sm text-sky-100 transition hover:bg-sky-400/10">Открыть</a>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if (in_array($task->status->value, ['assigned', 'returned'], true))
                                <form method="post" action="{{ route('admin.ndt-tasks.status.accept', $task) }}">
                                    @csrf
                                    @method('patch')
                                    <button class="rounded-full bg-sky-500 px-4 py-2 text-sm font-medium text-white">Принять</button>
                                </form>
                            @endif
                            @if ($task->status->value === 'accepted')
                                <form method="post" action="{{ route('admin.ndt-tasks.status.start', $task) }}">
                                    @csrf
                                    @method('patch')
                                    <button class="rounded-full bg-indigo-500 px-4 py-2 text-sm font-medium text-white">В работу</button>
                                </form>
                            @endif
                            @if ($task->status->value === 'in_work')
                                <form method="post" action="{{ route('admin.ndt-tasks.status.complete', $task) }}">
                                    @csrf
                                    @method('patch')
                                    <button class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-medium text-white">Завершить</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-white/15 bg-slate-900/40 p-6 text-sm text-slate-300">
                        Сейчас нет назначенных заданий.
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                <h2 class="text-lg font-semibold text-white">Быстрый вход</h2>
                <p class="mt-2 text-sm text-slate-300">Сначала откройте задание, затем переходите к web-карточке для деталей и файлов.</p>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-400">Подсказка</h3>
                <p class="mt-2 text-sm text-slate-300">Эта страница предназначена для коротких действий: принять задание, перейти в работу и закрыть результат.</p>
            </div>
        </aside>
    </div>
@endsection
