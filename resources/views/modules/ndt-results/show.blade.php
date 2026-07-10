@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Результаты контроля</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $result->weld?->weld_number }} · {{ $result->method?->code?->label() }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка результата, статусы, дефекты и метод-специфичная форма.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.4fr,1fr]">
            <div class="panel p-6 space-y-6">
                @can('update', $result)
                    <form method="post" action="{{ route('admin.ndt-results.update', $result) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="executor_employee_id">Исполнитель</label>
                            <select id="executor_employee_id" name="executor_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('executor_employee_id', $result->executor_employee_id) == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="control_date">Дата контроля</label>
                            <input id="control_date" type="date" name="control_date" value="{{ old('control_date', optional($result->control_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="normative_document_id">НТД</label>
                            <select id="normative_document_id" name="normative_document_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не указано</option>
                                @foreach ($normativeDocuments as $document)
                                    <option value="{{ $document->id }}" @selected(old('normative_document_id', $result->normative_document_id) == $document->id)>{{ $document->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="equipment_id">Оборудование</label>
                            <select id="equipment_id" name="equipment_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не указано</option>
                                @foreach ($equipment as $item)
                                    <option value="{{ $item->id }}" @selected(old('equipment_id', $result->equipment_id) == $item->id)>{{ $item->name }} — {{ $item->inventory_number ?: $item->serial_number ?: $item->status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="result_text">Результат</label>
                            <textarea id="result_text" name="result_text" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('result_text', $result->result_text) }}</textarea>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment', $result->comment) }}</textarea>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить результат</button>
                        </div>
                    </form>
                @endcan

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Задание</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $result->task?->task_number }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Стык</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $result->weld?->weld_number }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $result->weld?->object?->city?->name }} · {{ $result->weld?->object?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Метод</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $result->method?->code?->label() }} {{ $result->method?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $result->status->label() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Результат</p>
                        <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $result->result_text ?: 'Не заполнен' }}</p>
                    </div>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Действия</h2>

                @php($statusComment = old('comment'))
                @can('manage', $result)
                    <form method="post" action="{{ route('admin.ndt-results.status.analysis', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Передать на анализ</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Передать</button>
                    </form>
                @endcan

                @can('analyze', $result)
                    <form method="post" action="{{ route('admin.ndt-results.status.defect', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Отметить дефект</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Дефект</button>
                    </form>

                    <form method="post" action="{{ route('admin.ndt-results.status.ready', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Готов к заключению</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Готово</button>
                    </form>
                @endcan

                @can('approve', $result)
                    <form method="post" action="{{ route('admin.ndt-results.status.return', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Вернуть на доработку</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full border border-amber-200 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-50">Вернуть</button>
                    </form>

                    <form method="post" action="{{ route('admin.ndt-results.status.approve', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('patch')
                        <p class="text-sm font-medium text-slate-900">Утвердить результат</p>
                        <input name="comment" value="{{ $statusComment }}" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Утвердить</button>
                    </form>
                @endcan

                @can('analyze', $result)
                    <form method="post" action="{{ route('admin.ndt-results.defects.store', $result) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Добавить дефект</p>
                        <select name="defect_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                            <option value="">Тип дефекта не указан</option>
                            @foreach ($defectTypes as $defectType)
                                <option value="{{ $defectType->id }}" @selected(old('defect_type_id') == $defectType->id)>{{ $defectType->name }}</option>
                            @endforeach
                        </select>
                        <textarea name="description" rows="3" placeholder="Описание дефекта" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('description') }}</textarea>
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('comment') }}</textarea>
                        <button type="submit" class="rounded-full bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Сохранить дефект</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr,1fr]">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Дефекты</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Тип</th>
                                <th class="px-6 py-4">Описание</th>
                                <th class="px-6 py-4">Комментарий</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($result->defects as $defect)
                                <tr>
                                    <td class="px-6 py-5">{{ $defect->defectType?->name ?: 'Не указан' }}</td>
                                    <td class="px-6 py-5 whitespace-pre-line">{{ $defect->description }}</td>
                                    <td class="px-6 py-5 whitespace-pre-line">{{ $defect->comment }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Метод-специфичная форма</h2>

                @if ($result->method?->code?->value === 'vik')
                    @php($form = $result->vtResult)
                    <form method="post" action="{{ route('admin.ndt-results.vt.update', $result) }}" class="space-y-4">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="vik_conclusion_number">Номер заключения</label>
                            <input id="vik_conclusion_number" name="conclusion_number" value="{{ old('conclusion_number', $form?->conclusion_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="vik_conclusion_date">Дата</label>
                            <input id="vik_conclusion_date" type="date" name="conclusion_date" value="{{ old('conclusion_date', optional($form?->conclusion_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="vik_measurements">Измерения</label>
                            <textarea id="vik_measurements" name="measurements" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('measurements', $form?->measurements) }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="vik_transfer_register_number">Реестр передачи</label>
                            <input id="vik_transfer_register_number" name="transfer_register_number" value="{{ old('transfer_register_number', $form?->transfer_register_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="vik_act_number">Акт</label>
                            <input id="vik_act_number" name="act_number" value="{{ old('act_number', $form?->act_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить ВИК</button>
                    </form>
                @elseif ($result->method?->code?->value === 'pvk')
                    @php($form = $result->ptResult)
                    <form method="post" action="{{ route('admin.ndt-results.pt.update', $result) }}" class="space-y-4">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="pvk_conclusion_number">Номер заключения</label>
                            <input id="pvk_conclusion_number" name="conclusion_number" value="{{ old('conclusion_number', $form?->conclusion_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="pvk_conclusion_date">Дата</label>
                            <input id="pvk_conclusion_date" type="date" name="conclusion_date" value="{{ old('conclusion_date', optional($form?->conclusion_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="pvk_control_zone">Зона контроля</label>
                            <input id="pvk_control_zone" name="control_zone" value="{{ old('control_zone', $form?->control_zone) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="pvk_materials_used">Примененные материалы</label>
                            <textarea id="pvk_materials_used" name="materials_used" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('materials_used', $form?->materials_used) }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="pvk_transfer_register_number">Реестр передачи</label>
                            <input id="pvk_transfer_register_number" name="transfer_register_number" value="{{ old('transfer_register_number', $form?->transfer_register_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="pvk_act_number">Акт</label>
                            <input id="pvk_act_number" name="act_number" value="{{ old('act_number', $form?->act_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить ПВК</button>
                    </form>
                @elseif ($result->method?->code?->value === 'mk')
                    @php($form = $result->mtResult)
                    <form method="post" action="{{ route('admin.ndt-results.mt.update', $result) }}" class="space-y-4">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="mk_conclusion_number">Номер заключения</label>
                            <input id="mk_conclusion_number" name="conclusion_number" value="{{ old('conclusion_number', $form?->conclusion_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="mk_conclusion_date">Дата</label>
                            <input id="mk_conclusion_date" type="date" name="conclusion_date" value="{{ old('conclusion_date', optional($form?->conclusion_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="mk_control_zone">Зона контроля</label>
                            <input id="mk_control_zone" name="control_zone" value="{{ old('control_zone', $form?->control_zone) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="mk_material">Материал</label>
                            <input id="mk_material" name="material" value="{{ old('material', $form?->material) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="mk_control_parameters">Параметры контроля</label>
                            <textarea id="mk_control_parameters" name="control_parameters" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('control_parameters', $form?->control_parameters) }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="mk_transfer_register_number">Реестр передачи</label>
                            <input id="mk_transfer_register_number" name="transfer_register_number" value="{{ old('transfer_register_number', $form?->transfer_register_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="mk_act_number">Акт</label>
                            <input id="mk_act_number" name="act_number" value="{{ old('act_number', $form?->act_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить МК</button>
                    </form>
                @elseif ($result->method?->code?->value === 'uk')
                    @php($form = $result->utResult)
                    <form method="post" action="{{ route('admin.ndt-results.ut.update', $result) }}" class="space-y-4">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="uk_conclusion_number">Номер заключения</label>
                            <input id="uk_conclusion_number" name="conclusion_number" value="{{ old('conclusion_number', $form?->conclusion_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="uk_conclusion_date">Дата</label>
                            <input id="uk_conclusion_date" type="date" name="conclusion_date" value="{{ old('conclusion_date', optional($form?->conclusion_date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="uk_sounding_scheme">Схема прозвучивания</label>
                            <input id="uk_sounding_scheme" name="sounding_scheme" value="{{ old('sounding_scheme', $form?->sounding_scheme) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="uk_transducer">Преобразователь</label>
                            <input id="uk_transducer" name="transducer" value="{{ old('transducer', $form?->transducer) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="uk_tuning_parameters">Параметры настройки</label>
                            <textarea id="uk_tuning_parameters" name="tuning_parameters" rows="3" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">{{ old('tuning_parameters', $form?->tuning_parameters) }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="uk_transfer_register_number">Реестр передачи</label>
                            <input id="uk_transfer_register_number" name="transfer_register_number" value="{{ old('transfer_register_number', $form?->transfer_register_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="uk_act_number">Акт</label>
                            <input id="uk_act_number" name="act_number" value="{{ old('act_number', $form?->act_number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить УК</button>
                    </form>
                @else
                    <p class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Для РК форма этапа 7 не используется.
                    </p>
                @endif
            </div>
        </div>

        <div class="panel p-6 space-y-4">
            <h2 class="text-2xl font-semibold text-slate-900">История статусов</h2>
            <div class="space-y-3">
                @foreach ($result->statusHistory as $history)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="font-medium text-slate-900">{{ $history->from_status ?: '—' }} → {{ $history->to_status }}</p>
                            <p class="text-slate-500">{{ $history->created_at?->format('d.m.Y H:i') }}</p>
                        </div>
                        <p class="mt-2 text-slate-600">{{ $history->changedBy?->name ?: 'Система' }}@if($history->comment) · {{ $history->comment }}@endif</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
