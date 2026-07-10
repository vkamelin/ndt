@extends('layouts.pwa')

@section('title', 'Дешифровка')

@section('content')
    <div class="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
        <section class="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-2xl shadow-slate-950/25">
            <h2 class="text-lg font-semibold text-white">Очередь и текущая смена</h2>
            <p class="mt-1 text-sm text-slate-300">Просмотр материалов, фиксация брака и подозрений на подлог.</p>

            @if ($currentShift !== null)
                <div class="mt-4 rounded-2xl border border-violet-400/20 bg-violet-500/10 p-4">
                    <p class="text-sm text-violet-100">Смена №{{ $currentShift->id }} · {{ $currentShift->status->label() }}</p>
                    <p class="mt-1 text-sm text-slate-100">{{ $currentShift->started_at?->format('d.m.Y H:i') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('admin.shifts.show', $currentShift) }}" class="rounded-full bg-white px-4 py-2 text-sm font-medium text-slate-900">Открыть смену</a>
                        <form method="post" action="{{ route('admin.shifts.complete', $currentShift) }}">
                            @csrf
                            @method('patch')
                            <button class="rounded-full border border-white/20 px-4 py-2 text-sm font-medium text-white">Завершить</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="mt-4 rounded-2xl border border-dashed border-white/15 bg-slate-900/40 p-4">
                    <p class="text-sm text-slate-300">Активная смена не найдена.</p>
                    @if ($employee !== null)
                        <form method="post" action="{{ route('admin.shifts.store') }}" class="mt-4 flex flex-wrap gap-2">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                            <input type="hidden" name="type" value="decoder">
                            <input type="hidden" name="comment" value="Пуск смены дешифровщика из PWA">
                            <button class="rounded-full bg-violet-500 px-4 py-2 text-sm font-medium text-white">Открыть смену</button>
                        </form>
                    @endif
                </div>
            @endif
        </section>

        <aside class="space-y-4">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-400">Объект</h3>
                <p class="mt-2 text-base text-white">{{ $object?->name ?? 'Не определен' }}</p>
                <p class="text-sm text-slate-300">{{ $object?->city?->name ?? 'Город не указан' }}</p>
            </div>
            <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-400">Подсказка</h3>
                <p class="mt-2 text-sm text-slate-300">После просмотра материала удобнее перейти в полную карточку смены и зафиксировать детали.</p>
            </div>
        </aside>
    </div>
@endsection
