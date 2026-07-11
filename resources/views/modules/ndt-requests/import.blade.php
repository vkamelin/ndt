@extends('layouts.app')

@section('content')
    @php
        $requestData = $preview['request'] ?? [];
        $previewRows = $preview['rows'] ?? [];
    @endphp

    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заявки НК</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Импорт заявки</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Загрузите CSV или XLSX с перечнем стыков, проверьте предпросмотр и подтвердите создание заявки.
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

        <div class="grid gap-6 xl:grid-cols-[1.1fr,0.9fr]">
            <div class="panel p-6">
                <form id="import-form" method="post" action="{{ route('admin.ndt-requests.import.preview') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @if ($currentObject)
                        <input type="hidden" name="object_id" value="{{ $currentObject->id }}">
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="request_number">Номер заявки</label>
                            <input id="request_number" name="request_number" value="{{ old('request_number', $requestData['request_number'] ?? '') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="request_date">Дата заявки</label>
                            <input id="request_date" type="date" name="request_date" value="{{ old('request_date', $requestData['request_date'] ?? now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="title_id">Титул</label>
                            <select id="title_id" name="title_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($titles as $title)
                                    <option value="{{ $title->id }}" @selected((string) old('title_id', $requestData['title_id'] ?? '') === (string) $title->id)>{{ $title->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="priority">Приоритет</label>
                            <input id="priority" name="priority" value="{{ old('priority', $requestData['priority'] ?? '') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="due_date">Срок выполнения</label>
                            <input id="due_date" type="date" name="due_date" value="{{ old('due_date', $requestData['due_date'] ?? '') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-1">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="basis">Основание работ</label>
                            <textarea id="basis" name="basis" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('basis', $requestData['basis'] ?? '') }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment', $requestData['comment'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="file">Файл импорта CSV/XLSX</label>
                        <input id="file" type="file" name="file" accept=".csv,.xlsx" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-full file:border-0 file:bg-brand-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-700">
                        <p class="text-xs text-slate-500">Файл содержит только список стыков. Объект и заказчик берутся из карточки начальника участка.</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">
                            Посмотреть предпросмотр
                        </button>
                        <a href="{{ route('admin.ndt-requests.template.csv') }}" class="rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Скачать CSV
                        </a>
                        <a href="{{ route('admin.ndt-requests.template.xlsx') }}" class="rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Скачать XLSX
                        </a>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="panel p-6">
                    <h2 class="text-2xl font-semibold text-slate-900">Объект и заказчик</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        @if ($currentObject)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-slate-500">Объект/участок</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $currentObject->name }}</p>
                                <p class="mt-1 text-slate-500">{{ $currentObject->city?->name }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-slate-500">Заказчик</p>
                                <p class="mt-1 font-medium text-slate-900">{{ $currentObject->organization?->name ?: 'Не задан' }}</p>
                            </div>
                        @else
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                                <select id="object_id" name="object_id" form="import-form" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                    <option value="">Выберите объект</option>
                                    @foreach ($objects as $object)
                                        <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="panel p-6">
                    <h2 class="text-2xl font-semibold text-slate-900">Шаблон файла</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        В шаблоне оставлены только быстрые поля для стыков. Этого достаточно, чтобы автоматически зарегистрировать их в системе, а остальные данные заполнить позже на карточке стыка.
                    </p>
                </div>
            </div>
        </div>

        @if ($preview !== null)
            <div class="panel p-6 space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Предпросмотр импорта</h2>
                        <p class="mt-1 text-sm text-slate-600">Проверьте строки перед созданием заявки.</p>
                    </div>

                    <form method="post" action="{{ route('admin.ndt-requests.import.store') }}">
                        @csrf
                        <input type="hidden" name="import_token" value="{{ $importToken }}">
                        <button type="submit" class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">
                            Подтвердить импорт
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Номер стыка</th>
                                <th class="px-6 py-4">Диаметр</th>
                                <th class="px-6 py-4">Толщина</th>
                                <th class="px-6 py-4">Дата сварки</th>
                                <th class="px-6 py-4">PWHT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($previewRows as $row)
                                <tr>
                                    <td class="px-6 py-5 font-medium text-slate-900">{{ $row['weld_number'] ?? '—' }}</td>
                                    <td class="px-6 py-5 text-slate-600">{{ $row['diameter'] ?? '—' }}</td>
                                    <td class="px-6 py-5 text-slate-600">{{ $row['thickness'] ?? '—' }}</td>
                                    <td class="px-6 py-5 text-slate-600">{{ $row['welded_at'] ?? '—' }}</td>
                                    <td class="px-6 py-5 text-slate-600">{{ $row['pwht'] === true ? 'Да' : ($row['pwht'] === false ? 'Нет' : '—') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">В файле нет строк.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
