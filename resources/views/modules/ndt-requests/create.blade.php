@extends('layouts.app')

@section('content')
    @php
        $initialWelds = old('welds', [
            [
                'weld_number' => '',
                'diameter' => '',
                'thickness' => '',
                'welded_at' => '',
                'pwht' => '',
            ],
        ]);
    @endphp

    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заявки НК</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Создание заявки</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Начальник участка заполняет заявку и сразу регистрирует стыки, которые нужно проинспектировать.
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

        <div class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
            <div class="panel p-6">
                <form id="request-form" method="post" action="{{ route('admin.ndt-requests.store') }}" class="space-y-6" x-data="requestRows(@js($initialWelds))">
                    @csrf

                    @if ($currentObject)
                        <input type="hidden" name="object_id" value="{{ $currentObject->id }}">
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="request_number">Номер заявки</label>
                            <input id="request_number" name="request_number" value="{{ old('request_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="request_date">Дата заявки</label>
                            <input id="request_date" type="date" name="request_date" value="{{ old('request_date', now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="title_id">Титул</label>
                            <select id="title_id" name="title_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($titles as $title)
                                    <option value="{{ $title->id }}" @selected(old('title_id') == $title->id)>{{ $title->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="priority">Приоритет</label>
                            <input id="priority" name="priority" value="{{ old('priority') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="due_date">Срок выполнения</label>
                            <input id="due_date" type="date" name="due_date" value="{{ old('due_date') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-1">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="basis">Основание работ</label>
                            <textarea id="basis" name="basis" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('basis') }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment') }}</textarea>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">Стыки заявки</h2>
                                <p class="mt-1 text-sm text-slate-600">Добавьте все стыки сразу, чтобы они автоматически зарегистрировались в системе.</p>
                            </div>
                            <button type="button" @click="addRow()" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Добавить стык
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Стык <span x-text="index + 1"></span></p>
                                        <button type="button" @click="removeRow(index)" class="rounded-full border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                            Удалить
                                        </button>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-slate-700" :for="`weld_number_${index}`">Номер стыка</label>
                                            <input :id="`weld_number_${index}`" :name="`welds[${index}][weld_number]`" x-model="row.weld_number" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-slate-700" :for="`diameter_${index}`">Диаметр</label>
                                            <input :id="`diameter_${index}`" :name="`welds[${index}][diameter]`" x-model="row.diameter" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-slate-700" :for="`thickness_${index}`">Толщина</label>
                                            <input :id="`thickness_${index}`" :name="`welds[${index}][thickness]`" x-model="row.thickness" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-slate-700" :for="`welded_at_${index}`">Дата сварки</label>
                                            <input :id="`welded_at_${index}`" type="date" :name="`welds[${index}][welded_at]`" x-model="row.welded_at" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                        </div>
                                        <div class="space-y-2 md:col-span-2 xl:col-span-1">
                                            <label class="text-sm font-medium text-slate-700" :for="`pwht_${index}`">PWHT</label>
                                            <select :id="`pwht_${index}`" :name="`welds[${index}][pwht]`" x-model="row.pwht" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                <option value="">Не указано</option>
                                                <option value="1">Да</option>
                                                <option value="0">Нет</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">
                            Создать заявку
                        </button>
                        <a href="{{ route('admin.ndt-requests.index') }}" class="rounded-full border border-slate-200 bg-white px-5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Отмена
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
                                <select id="object_id" name="object_id" form="request-form" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                    <option value="">Выберите объект</option>
                                    @foreach ($objects as $object)
                                        <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-sm text-slate-500">Заказчик будет подставлен по выбранному объекту.</p>
                        @endif
                    </div>
                </div>

                <div class="panel p-6">
                    <h2 class="text-2xl font-semibold text-slate-900">Подсказка</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Если у вас уже есть список стыков в таблице, используйте импорт. Для быстрой проверки или срочной регистрации можно добавить несколько строк вручную прямо здесь.
                    </p>
                    <div class="mt-4">
                        <a href="{{ route('admin.ndt-requests.import') }}" class="rounded-full border border-brand-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                            Перейти к импорту
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
