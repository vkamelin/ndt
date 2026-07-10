@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заключение</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $conclusion->number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка заключения, его версии, файлы, история статусов и действия по жизненному циклу.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.45fr,1fr]">
            <div class="panel p-6 space-y-6">
                @can('manage', $conclusion)
                    <form method="post" action="{{ route('admin.conclusions.update', $conclusion) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                            <input id="number" name="number" value="{{ old('number', $conclusion->number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="date">Дата</label>
                            <input id="date" type="date" name="date" value="{{ old('date', optional($conclusion->date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="xl:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment', $conclusion->comment) }}</textarea>
                        </div>
                        <div class="xl:col-span-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                        </div>
                    </form>
                @endcan

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $conclusion->status->label() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Объект/участок</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $conclusion->object?->name ?: 'Не указан' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $conclusion->object?->city?->name ?: 'Без города' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Метод</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $conclusion->method?->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $conclusion->request?->request_number ?: 'Без заявки' }}</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Подготовил</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $conclusion->preparedBy?->fullName() ?: 'Не указан' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Проверил</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $conclusion->checkedBy?->fullName() ?: 'Не указан' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Утвердил</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $conclusion->approvedBy?->fullName() ?: 'Не указан' }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <h2 class="text-2xl font-semibold text-slate-900">Позиции</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">№</th>
                                    <th class="px-6 py-4">Стык</th>
                                    <th class="px-6 py-4">Метод</th>
                                    <th class="px-6 py-4">Результат</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($conclusion->items as $item)
                                    <tr>
                                        <td class="px-6 py-5">{{ $item->sort_order }}</td>
                                        <td class="px-6 py-5">{{ $item->result?->weld?->weld_number ?: 'Не указан' }}</td>
                                        <td class="px-6 py-5">{{ $item->result?->method?->name ?: 'Не указан' }}</td>
                                        <td class="px-6 py-5">{{ $item->result?->status->label() ?: 'Не указан' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Позиции не добавлены.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                @can('manage', $conclusion)
                    <div class="panel p-6 space-y-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Действия</h2>

                        <form method="post" action="{{ route('admin.conclusions.submit', $conclusion) }}" class="space-y-3">
                            @csrf
                            <textarea name="comment" rows="2" placeholder="Комментарий к отправке" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Отправить на проверку</button>
                        </form>

                        <form method="post" action="{{ route('admin.conclusions.files.store', $conclusion) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <p class="text-sm font-medium text-slate-900">Прикрепить файл</p>
                            <input type="file" name="file" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Загрузить файл</button>
                        </form>
                    </div>
                @endcan

                @can('version', $conclusion)
                    <div class="panel p-6 space-y-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Версия</h2>
                        <form method="post" action="{{ route('admin.conclusions.versions.store', $conclusion) }}" class="space-y-3">
                            @csrf
                            <textarea name="basis" rows="2" placeholder="Основание для новой версии" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Поставить версию в очередь</button>
                        </form>
                    </div>
                @endcan

                @can('approve', $conclusion)
                    <div class="panel p-6 space-y-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Проверка</h2>
                        <form method="post" action="{{ route('admin.conclusions.approve', $conclusion) }}" class="space-y-3">
                            @csrf
                            @method('patch')
                            <textarea name="comment" rows="2" placeholder="Комментарий к утверждению" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Утвердить</button>
                        </form>
                        <form method="post" action="{{ route('admin.conclusions.return', $conclusion) }}" class="space-y-3">
                            @csrf
                            @method('patch')
                            <textarea name="comment" rows="2" placeholder="Причина возврата" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-700">Вернуть на доработку</button>
                        </form>
                    </div>
                @endcan

                @can('issue', $conclusion)
                    <div class="panel p-6 space-y-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Выдача</h2>
                        <form method="post" action="{{ route('admin.conclusions.issue', $conclusion) }}" class="space-y-3">
                            @csrf
                            @method('patch')
                            <input name="basis" value="Выдача заключения" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <button type="submit" class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Выдать</button>
                        </form>
                    </div>
                @endcan

                @can('replace', $conclusion)
                    <div class="panel p-6 space-y-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Замена</h2>
                        <form method="post" action="{{ route('admin.conclusions.replace', $conclusion) }}" class="grid gap-3">
                            @csrf
                            <input name="number" value="{{ $conclusion->number }}-R" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm" placeholder="Новый номер">
                            <input type="date" name="date" value="{{ today()->toDateString() }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <textarea name="reason" rows="2" placeholder="Причина замены" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Создать замену</button>
                        </form>
                    </div>
                @endcan

                @can('annul', $conclusion)
                    <div class="panel p-6 space-y-3">
                        <h2 class="text-2xl font-semibold text-slate-900">Аннулирование</h2>
                        <form method="post" action="{{ route('admin.conclusions.annul', $conclusion) }}" class="space-y-3">
                            @csrf
                            @method('patch')
                            <textarea name="reason" rows="2" placeholder="Причина аннулирования" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                            <button type="submit" class="rounded-full border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-700">Аннулировать</button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Файлы</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Имя</th>
                                <th class="px-6 py-4">Размер</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($conclusion->files as $file)
                                <tr>
                                    <td class="px-6 py-5">{{ $file->original_name }}</td>
                                    <td class="px-6 py-5">{{ number_format($file->size / 1024, 1, '.', ' ') }} KB</td>
                                    <td class="px-6 py-5">
                                        <a href="{{ route('admin.files.download', $file) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">Скачать</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-500">Файлы не прикреплены.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Версии</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Версия</th>
                                <th class="px-6 py-4">Основание</th>
                                <th class="px-6 py-4">Статус</th>
                                <th class="px-6 py-4">Файл</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($conclusion->versions as $version)
                                <tr>
                                    <td class="px-6 py-5">v{{ $version->version_number }}</td>
                                    <td class="px-6 py-5">{{ $version->basis }}</td>
                                    <td class="px-6 py-5">{{ $version->status->label() }}</td>
                                    <td class="px-6 py-5">
                                        <a href="{{ route('admin.files.download', $version->file) }}" class="font-medium text-brand-700 transition hover:text-brand-800">{{ $version->file?->original_name }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Версии отсутствуют.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4 xl:col-span-2">
                <h2 class="text-2xl font-semibold text-slate-900">История статусов</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">С</th>
                                <th class="px-6 py-4">На</th>
                                <th class="px-6 py-4">Кто</th>
                                <th class="px-6 py-4">Комментарий</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($conclusion->statusHistory as $history)
                                <tr>
                                    <td class="px-6 py-5">{{ $history->from_status ?: 'n/a' }}</td>
                                    <td class="px-6 py-5">{{ $history->to_status }}</td>
                                    <td class="px-6 py-5">{{ $history->changedBy?->name ?: 'Система' }}</td>
                                    <td class="px-6 py-5">{{ $history->comment ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">История статусов пуста.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
