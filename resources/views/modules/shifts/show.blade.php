@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Смена</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $shift->type->label() }} · {{ $shift->employee?->fullName() }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Журнал операций по смене и формы для регистрации отчетов, движений и завершения.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.25fr,1fr]">
            <div class="panel p-6 space-y-4">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Объект</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $shift->object?->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $shift->object?->city?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $shift->status->label() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Старт</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $shift->started_at?->format('d.m.Y H:i') }}</p>
                    </div>
                </div>

                @can('manage', $shift)
                    <form method="post" action="{{ route('admin.shifts.complete', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Завершение смены</p>
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Завершить</button>
                    </form>
                @endcan

                @if ($shift->type->value === 'lab')
                    @can('manage', $shift)
                        <div class="grid gap-4 md:grid-cols-2">
                            <form method="post" action="{{ route('admin.shifts.lab.reports.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Отчет смены лаборанта</p>
                                <textarea name="summary" rows="3" placeholder="Итог" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.lab.regulatory-works.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Регламентные работы</p>
                                <input name="description" placeholder="Описание" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.lab.film-transactions.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Движение пленки</p>
                                <input name="rt_film_id" type="number" min="1" placeholder="ID пленки" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <input name="quantity" type="number" min="1" value="1" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.lab.chemical-transactions.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Движение химии</p>
                                <input name="chemical_type_id" type="number" min="1" placeholder="ID химии" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <input name="quantity" type="number" min="1" value="1" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.lab.chemical-requests.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Запрос химии</p>
                                <input name="chemical_type_id" type="number" min="1" placeholder="ID химии" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <input name="quantity" type="number" min="1" value="1" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Создать</button>
                            </form>
                        </div>
                    @endcan
                @else
                    @can('manage', $shift)
                        <div class="grid gap-4 md:grid-cols-2">
                            <form method="post" action="{{ route('admin.shifts.decoder.reports.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Отчет смены дешифровщика</p>
                                <textarea name="summary" rows="3" placeholder="Итог" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.decoder.cleanups.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Очистка рабочего места</p>
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.decoder.film-groups.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Просмотренная группа</p>
                                <input name="group_name" placeholder="Название группы" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <input name="rt_result_id" type="number" min="1" placeholder="ID РК-результата" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.decoder.rejects.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Брак</p>
                                <input name="reason" placeholder="Причина" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <input name="rt_result_id" type="number" min="1" placeholder="ID РК-результата" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.decoder.forgery-suspicions.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Подлог</p>
                                <input name="reason" placeholder="Причина" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <input name="rt_result_id" type="number" min="1" placeholder="ID РК-результата" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>

                            <form method="post" action="{{ route('admin.shifts.decoder.decryptions.store', $shift) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                                @csrf
                                <p class="text-sm font-medium text-slate-900">Дешифровка</p>
                                <textarea name="result_text" rows="3" placeholder="Результат дешифровки" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <textarea name="analysis_comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                                <input name="rt_result_id" type="number" min="1" placeholder="ID РК-результата" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                            </form>
                        </div>
                    @endcan
                @endif
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Сводка</h2>
                <div class="space-y-3 text-sm">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">Регламентные работы: {{ $shift->labRegulatoryWorks->count() }}</p>
                        <p class="mt-1 text-slate-600">Пленки: {{ $shift->filmTransactions->count() }}</p>
                        <p class="mt-1 text-slate-600">Химия: {{ $shift->chemicalTransactions->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">Запросы химии: {{ $shift->chemicalRequests->count() }}</p>
                        <p class="mt-1 text-slate-600">Группы: {{ $shift->decoderFilmGroups->count() }}</p>
                        <p class="mt-1 text-slate-600">Дешифровки: {{ $shift->decoderDecryptions->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Лабораторный журнал</h2>
                @foreach ($shift->labRegulatoryWorks as $work)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $work->description }}</p>
                        <p class="mt-1 text-slate-600">{{ $work->worked_at?->format('d.m.Y H:i') }}</p>
                    </div>
                @endforeach
                @foreach ($shift->filmTransactions as $transaction)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $transaction->operation }} · {{ $transaction->quantity }}</p>
                        <p class="mt-1 text-slate-600">{{ $transaction->transacted_at?->format('d.m.Y H:i') }}</p>
                    </div>
                @endforeach
                @foreach ($shift->chemicalRequests as $request)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">Запрос химии · {{ $request->quantity }}</p>
                        <p class="mt-1 text-slate-600">{{ $request->status }}</p>
                    </div>
                @endforeach
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Дешифровочный журнал</h2>
                @foreach ($shift->decoderFilmGroups as $group)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $group->group_name }}</p>
                        <p class="mt-1 text-slate-600">{{ $group->viewed_at?->format('d.m.Y H:i') }}</p>
                    </div>
                @endforeach
                @foreach ($shift->decoderRejects as $reject)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $reject->reason }}</p>
                        <p class="mt-1 text-slate-600">{{ $reject->recorded_at?->format('d.m.Y H:i') }}</p>
                    </div>
                @endforeach
                @foreach ($shift->decoderForgerySuspicion as $suspicion)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $suspicion->reason }}</p>
                        <p class="mt-1 text-slate-600">{{ $suspicion->recorded_at?->format('d.m.Y H:i') }}</p>
                    </div>
                @endforeach
                @foreach ($shift->decoderDecryptions as $decryption)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $decryption->result_text ?: 'Без результата' }}</p>
                        <p class="mt-1 text-slate-600">{{ $decryption->decrypted_at?->format('d.m.Y H:i') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
