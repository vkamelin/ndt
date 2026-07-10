@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Документ</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $document->type?->name }} {{ $document->number ?: '' }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка документа с файлами, версиями и связями.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.4fr,1fr]">
            <div class="panel p-6 space-y-6">
                @can('manage', $document)
                    <form method="post" action="{{ route('admin.documents.update', $document) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="document_type_id">Тип документа</label>
                            <select id="document_type_id" name="document_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($documentTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('document_type_id', $document->document_type_id) == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                            <input id="number" name="number" value="{{ old('number', $document->number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="document_date">Дата</label>
                            <input id="document_date" type="date" name="document_date" value="{{ old('document_date', optional($document->document_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                            <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $document->status->value) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="organization_id">Организация</label>
                            <select id="organization_id" name="organization_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Не указана</option>
                                @foreach ($organizations as $organization)
                                    <option value="{{ $organization->id }}" @selected(old('organization_id', $document->organization_id) == $organization->id)>{{ $organization->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="city_id">Город</label>
                            <select id="city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Не указан</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @selected(old('city_id', $document->city_id) == $city->id)>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Не указан</option>
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected(old('object_id', $document->object_id) == $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="employee_id">Сотрудник</label>
                            <select id="employee_id" name="employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Не указан</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('employee_id', $document->employee_id) == $employee->id)>{{ $employee->fullName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="equipment_id">Оборудование</label>
                            <select id="equipment_id" name="equipment_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Не указано</option>
                                @foreach ($equipment as $item)
                                    <option value="{{ $item->id }}" @selected(old('equipment_id', $document->equipment_id) == $item->id)>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="ndt_request_id">Заявка</label>
                            <select id="ndt_request_id" name="ndt_request_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Не указана</option>
                                @foreach ($requests as $requestItem)
                                    <option value="{{ $requestItem->id }}" @selected(old('ndt_request_id', $document->ndt_request_id) == $requestItem->id)>{{ $requestItem->request_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="valid_until">Действует до</label>
                            <input id="valid_until" type="date" name="valid_until" value="{{ old('valid_until', optional($document->valid_until)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="md:col-span-2 xl:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment', $document->comment) }}</textarea>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                        </div>
                    </form>
                @endcan

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Тип</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $document->type?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $document->status->label() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Объект/участок</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $document->object?->name ?: 'Не указан' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $document->object?->city?->name ?: 'Без города' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Связь</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $document->organization?->name ?: 'Без организации' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $document->request?->request_number ?: 'Без заявки' }}</p>
                    </div>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                @can('manage', $document)
                    <form method="post" action="{{ route('admin.files.store') }}" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <input type="hidden" name="document_id" value="{{ $document->id }}">
                        <p class="text-sm font-medium text-slate-900">Прикрепить файл</p>
                        <input type="file" name="file" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Загрузить файл</button>
                    </form>
                @endcan

                @can('manage', $document)
                    <form method="post" action="{{ route('admin.documents.versions.store', $document) }}" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Добавить версию</p>
                        <input type="file" name="file" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="basis" rows="2" placeholder="Основание создания версии" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Создать версию</button>
                    </form>
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
                                <th class="px-6 py-4">Загрузил</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($document->files as $file)
                                <tr>
                                    <td class="px-6 py-5">
                                        <p class="font-medium text-slate-900">{{ $file->original_name }}</p>
                                        <p class="mt-1 text-slate-500">{{ $file->mime_type }}</p>
                                    </td>
                                    <td class="px-6 py-5">{{ number_format($file->size / 1024, 1, '.', ' ') }} KB</td>
                                    <td class="px-6 py-5">{{ $file->uploadedBy?->name ?: 'Система' }}</td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.files.download', $file) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">Скачать</a>
                                            @can('delete', $file)
                                                <form method="post" action="{{ route('admin.files.destroy', $file) }}">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Аннулировать</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Файлы не прикреплены.</td>
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
                                <th class="px-6 py-4">Файл</th>
                                <th class="px-6 py-4">Статус</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($document->versions as $version)
                                <tr>
                                    <td class="px-6 py-5">v{{ $version->version_number }}</td>
                                    <td class="px-6 py-5">{{ $version->basis }}</td>
                                    <td class="px-6 py-5">
                                        <a href="{{ route('admin.files.download', $version->file) }}" class="font-medium text-brand-700 transition hover:text-brand-800">
                                            {{ $version->file?->original_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-5">{{ $version->status->label() }}</td>
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
                <h2 class="text-2xl font-semibold text-slate-900">Связи</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Тип</th>
                                <th class="px-6 py-4">ID</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($document->relations as $relation)
                                <tr>
                                    <td class="px-6 py-5">{{ class_basename($relation->related_type) }}</td>
                                    <td class="px-6 py-5">{{ $relation->related_id }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-8 text-center text-sm text-slate-500">Связи не заданы.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
