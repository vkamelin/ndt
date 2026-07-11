@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Оборудование</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Добавление оборудования</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма создания перенесена с общего списка.
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
            <form method="post" action="{{ route('admin.equipment.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="equipment_type_id">Тип</label>
                    <select id="equipment_type_id" name="equipment_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($equipmentTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('equipment_type_id') == $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                    <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($objects as $object)
                            <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="name">Наименование</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="inventory_number">Инвентарный номер</label>
                    <input id="inventory_number" name="inventory_number" value="{{ old('inventory_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="serial_number">Серийный номер</label>
                    <input id="serial_number" name="serial_number" value="{{ old('serial_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="manufacturer">Производитель</label>
                    <input id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="model">Модель</label>
                    <input id="model" name="model" value="{{ old('model') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="purchased_at">Дата покупки</label>
                    <input id="purchased_at" type="date" name="purchased_at" value="{{ old('purchased_at') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2 xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                </div>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Добавить оборудование</button>
                    <a href="{{ route('admin.equipment.index') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
