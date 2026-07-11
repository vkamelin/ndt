@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Радиографический контроль</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $result->ndtResult?->weld?->weld_number }} · {{ $result->status->label() }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка РК с пленками, снимками, пересветами, плотностями и архивом.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.25fr,1fr]">
            <div class="panel p-6 space-y-6">
                @can('manage', $result)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">Редактирование карты РК</p>
                                <p class="mt-1 text-sm text-slate-600">Основная форма перенесена на отдельную страницу.</p>
                            </div>
                            <a href="{{ route('admin.radiography.edit', $result) }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Редактировать карту РК</a>
                        </div>
                    </div>
                @endcan

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Тип пленки</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $result->filmType?->name ?: 'Не указан' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Баркод</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $result->barcode ?: 'Не указан' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Архив</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $result->archive_location ?: 'Не указан' }}</p>
                    </div>
                </div>

                @can('manage', $result)
                    <form method="post" action="{{ route('admin.radiography.status.update', $result) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                            <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($result->status->value === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 xl:col-span-2 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <input id="comment" name="comment" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Обновить статус</button>
                        </div>
                    </form>
                @endcan

                @can('manage', $result)
                    <div class="grid gap-4 md:grid-cols-2">
                        <form method="post" action="{{ route('admin.radiography.films.store', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            @csrf
                            <p class="text-sm font-medium text-slate-900">Добавить пленку</p>
                            <input name="barcode" placeholder="Баркод" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <input name="position_number" type="number" min="1" placeholder="Позиция" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <select name="film_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Тип пленки</option>
                                @foreach ($filmTypes as $filmType)
                                    <option value="{{ $filmType->id }}">{{ $filmType->name }}</option>
                                @endforeach
                            </select>
                            <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Добавить</button>
                        </form>

                        <form method="post" action="{{ route('admin.radiography.reshoots.store', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            @csrf
                            <p class="text-sm font-medium text-slate-900">Пересвет</p>
                            <input name="reason" placeholder="Причина" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <input name="rt_film_id" type="number" min="1" placeholder="ID пленки" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full border border-amber-200 px-4 py-2 text-sm font-medium text-amber-700">Зафиксировать</button>
                        </form>

                        <form method="post" action="{{ route('admin.radiography.densities.store', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            @csrf
                            <p class="text-sm font-medium text-slate-900">Плотность</p>
                            <input name="density" type="number" step="0.001" placeholder="Плотность" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <input name="minimum_density" type="number" step="0.001" placeholder="Минимум" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <input name="maximum_density" type="number" step="0.001" placeholder="Максимум" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                        </form>

                        <form method="post" action="{{ route('admin.radiography.archive-items.store', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            @csrf
                            <p class="text-sm font-medium text-slate-900">Архив</p>
                            <input name="register_number" placeholder="Номер реестра" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <input name="archive_location" placeholder="Место хранения" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Добавить</button>
                        </form>
                    </div>
                @endcan
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Журналы</h2>
                <div class="space-y-3 text-sm">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">Пленки: {{ $result->films->count() }}</p>
                        <p class="mt-1 text-slate-600">Снимки: {{ $result->films->sum(fn ($film) => $film->images->count()) }}</p>
                        <p class="mt-1 text-slate-600">Экспозиции: {{ $result->exposures->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">Пересветы: {{ $result->reshoots->count() }}</p>
                        <p class="mt-1 text-slate-600">Плотности: {{ $result->densityMeasurements->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">Архивные позиции: {{ $result->archiveItems->count() }}</p>
                        <p class="mt-1 text-slate-600">{{ $result->latestArchiveItem?->archive_location ?: 'Позиция не задана' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Пленки и снимки</h2>
                @foreach ($result->films as $film)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="font-medium text-slate-900">Пленка {{ $film->position_number ?: 'без позиции' }}</p>
                                <p class="mt-1 text-slate-600">{{ $film->barcode ?: 'Без баркода' }}</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">Экспозиций {{ $film->exposure_count }}</span>
                        </div>

                        <form method="post" action="{{ route('admin.radiography.images.store', $film) }}" class="grid gap-3 md:grid-cols-3">
                            @csrf
                            <input name="file_id" type="number" min="1" placeholder="ID файла" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <input name="sequence_number" type="number" min="1" placeholder="Номер снимка" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Снимок</button>
                        </form>

                        <form method="post" action="{{ route('admin.radiography.exposures.store', $film) }}" class="grid gap-3 md:grid-cols-3">
                            @csrf
                            <input name="rt_result_id" type="hidden" value="{{ $result->id }}">
                            <input name="exposure_number" type="number" min="1" placeholder="Номер экспозиции" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <input name="exposed_at" type="datetime-local" class="rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Экспозиция</button>
                        </form>

                        <div class="space-y-2 text-sm">
                            @foreach ($film->images as $image)
                                <div class="rounded-xl bg-white px-4 py-3 text-slate-700">Снимок {{ $image->sequence_number }} {{ $image->file?->original_name ? '· '.$image->file->original_name : '' }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Плотности и пересветы</h2>
                <div class="space-y-3 text-sm">
                    @foreach ($result->densityMeasurements as $measurement)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">Плотность {{ $measurement->density }}</p>
                            <p class="mt-1 text-slate-600">Мин. {{ $measurement->minimum_density }} · Макс. {{ $measurement->maximum_density }}</p>
                        </div>
                    @endforeach
                    @foreach ($result->reshoots as $reshoot)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">{{ $reshoot->reason }}</p>
                            <p class="mt-1 text-slate-600">{{ $reshoot->comment }}</p>
                        </div>
                    @endforeach
                    @foreach ($result->archiveItems as $archiveItem)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">{{ $archiveItem->register_number ?: 'Без номера реестра' }}</p>
                            <p class="mt-1 text-slate-600">{{ $archiveItem->archive_location ?: 'Без места хранения' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
