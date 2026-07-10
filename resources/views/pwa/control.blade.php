@extends('layouts.pwa')

@section('title', 'Контроль участка')

@section('content')
    <div class="grid gap-4 xl:grid-cols-2">
        <section class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/25">
            <h2 class="text-lg font-semibold text-white">Активные задания</h2>
            <p class="mt-1 text-sm text-slate-300">Просроченные и незавершенные задания по объекту.</p>

            <div class="mt-4 space-y-3">
                @forelse ($tasks as $task)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-semibold text-white">{{ $task->task_number }}</span>
                            <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs text-slate-200">{{ $task->status->label() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-300">{{ $task->method?->name ?? 'Метод не указан' }} · {{ $task->planned_date?->format('d.m.Y') ?? 'Без срока' }}</p>
                        <a href="{{ route('admin.ndt-tasks.show', $task) }}" class="mt-3 inline-flex rounded-full border border-sky-400/30 px-4 py-2 text-sm text-sky-100 transition hover:bg-sky-400/10">Открыть</a>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-white/15 bg-slate-900/40 p-6 text-sm text-slate-300">
                        Активных заданий нет.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/25">
            <h2 class="text-lg font-semibold text-white">Результаты к анализу</h2>
            <p class="mt-1 text-sm text-slate-300">Материалы, ожидающие контроля или утверждения.</p>

            <div class="mt-4 space-y-3">
                @forelse ($results as $result)
                    <article class="rounded-2xl border border-white/10 bg-slate-900/60 p-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-semibold text-white">Результат #{{ $result->id }}</span>
                            <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs text-slate-200">{{ $result->status->label() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-300">{{ $result->weld?->weld_number ?? 'Без стыка' }} · {{ $result->method?->name ?? 'Метод не указан' }}</p>
                        <a href="{{ route('admin.ndt-results.show', $result) }}" class="mt-3 inline-flex rounded-full border border-violet-400/30 px-4 py-2 text-sm text-violet-100 transition hover:bg-violet-400/10">Открыть</a>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-white/15 bg-slate-900/40 p-6 text-sm text-slate-300">
                        Результатов на контроле нет.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
