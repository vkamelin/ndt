@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Документы и файлы</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Единый реестр документов</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Здесь хранятся карточки документов, их версии и прикрепленные файлы. Скачивание выполняется только через backend.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.documents.index') }}" class="grid gap-4 md:grid-cols-4">
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="document_type_id">Тип</label>
                    <select id="document_type_id" name="document_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($documentTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) request('document_type_id') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected((string) request('status') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                    <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($objects as $object)
                            <option value="{{ $object->id }}" @selected((string) request('object_id') === (string) $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить</button>
                </div>
            </form>
        </div>

        @can('document.manage')
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Создание документа</h2>
                        <p class="mt-2 text-sm text-slate-600">Большая форма перенесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.documents.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать документ</a>
                </div>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Документ</th>
                            <th class="px-6 py-4">Связь</th>
                            <th class="px-6 py-4">Срок</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Версии</th>
                            <th class="px-6 py-4">Файлы</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($documents as $document)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $document->type?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $document->number ?: 'Без номера' }}</p>
                                    <p class="mt-1 text-slate-500">{{ $document->document_date?->format('d.m.Y') }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $document->object?->name ?: 'Не связан с объектом' }}</p>
                                    <p class="mt-1 text-slate-500">{{ $document->organization?->name ?: 'Без организации' }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $document->valid_until?->format('d.m.Y') ?: 'Не указан' }}</td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $document->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">{{ $document->versions_count ?? $document->versions()->count() }}</td>
                                <td class="px-6 py-5">{{ $document->files_count ?? $document->files()->count() }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.documents.show', $document) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-500">Документы не найдены.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $documents->links() }}
    </div>
@endsection
