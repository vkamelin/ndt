@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Документы и файлы</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Создание документа</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма создания вынесена отдельно от списка.
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

        <div class="panel p-6">
            <form method="post" action="{{ route('admin.documents.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="document_type_id">Тип документа</label>
                    <select id="document_type_id" name="document_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($documentTypes as $documentType)
                            <option value="{{ $documentType->id }}" @selected(old('document_type_id') == $documentType->id)>{{ $documentType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                    <input id="number" name="number" value="{{ old('number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="document_date">Дата документа</label>
                    <input id="document_date" type="date" name="document_date" value="{{ old('document_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="valid_until">Действует до</label>
                    <input id="valid_until" type="date" name="valid_until" value="{{ old('valid_until') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="organization_id">Организация</label>
                    <select id="organization_id" name="organization_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected(old('organization_id') == $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="city_id">Город</label>
                    <select id="city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id') == $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                    <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($objects as $object)
                            <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="employee_id">Сотрудник</label>
                    <select id="employee_id" name="employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="equipment_id">Оборудование</label>
                    <select id="equipment_id" name="equipment_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($equipment as $item)
                            <option value="{{ $item->id }}" @selected(old('equipment_id') == $item->id)>{{ $item->name }} — {{ $item->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="ndt_request_id">Заявка НК</label>
                    <select id="ndt_request_id" name="ndt_request_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Не указано</option>
                        @foreach ($requests as $requestItem)
                            <option value="{{ $requestItem->id }}" @selected(old('ndt_request_id') == $requestItem->id)>{{ $requestItem->request_number }} — {{ $requestItem->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Создать документ</button>
                    <a href="{{ route('admin.documents.index') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
