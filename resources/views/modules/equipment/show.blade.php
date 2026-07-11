@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Оборудование</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $equipment->name }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка оборудования с журналами поверок, калибровок, ремонтов, выдач, возвратов, перемещений, дефектов и документов.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.4fr,1fr]">
            <div class="panel p-6 space-y-6">
                @can('manage', $equipment)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-900">Редактирование оборудования</p>
                                <p class="mt-1 text-sm text-slate-600">Основная форма перенесена на отдельную страницу.</p>
                            </div>
                            <a href="{{ route('admin.equipment.edit', $equipment) }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Редактировать оборудование</a>
                        </div>
                    </div>
                @endcan

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Объект/участок</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $equipment->object?->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $equipment->object?->city?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Тип</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $equipment->type?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $equipment->status->label() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Номера</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $equipment->inventory_number ?: 'Без инвентарного номера' }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $equipment->serial_number ?: 'Без серийного номера' }}</p>
                    </div>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Служебные действия</h2>

                @can('manage', $equipment)
                    <form method="post" action="{{ route('admin.equipment.destroy', $equipment) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        @method('delete')
                        <p class="text-sm font-medium text-slate-900">Списание</p>
                        <textarea name="comment" rows="2" placeholder="Причина списания" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700">Списать</button>
                    </form>
                @endcan

                @can('manage', $equipment)
                    <form method="post" action="{{ route('admin.equipment.verifications.store', $equipment) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Поверка</p>
                        <input type="date" name="verified_at" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input type="date" name="valid_until" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input name="certificate_number" placeholder="Номер свидетельства" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                    </form>
                @endcan

                @can('manage', $equipment)
                    <form method="post" action="{{ route('admin.equipment.calibrations.store', $equipment) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Калибровка</p>
                        <input type="date" name="calibrated_at" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input type="date" name="valid_until" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input name="certificate_number" placeholder="Номер свидетельства" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                    </form>
                @endcan

                @can('manage', $equipment)
                    <form method="post" action="{{ route('admin.equipment.assignments.store', $equipment) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Выдача</p>
                        <select name="employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="issued_at" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Выдать</button>
                    </form>
                @endcan

                @can('manage', $equipment)
                    <form method="post" action="{{ route('admin.equipment.movements.store', $equipment) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Перемещение</p>
                        <select name="to_object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}">{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                            @endforeach
                        </select>
                        <input type="date" name="moved_at" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Переместить</button>
                    </form>
                @endcan

                @can('manage', $equipment)
                    <form method="post" action="{{ route('admin.equipment.defects.store', $equipment) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Дефект</p>
                        <input type="date" name="detected_at" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="description" rows="3" placeholder="Описание" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700">Зафиксировать</button>
                    </form>
                @endcan

                @can('manage', $equipment)
                    <form method="post" action="{{ route('admin.equipment.documents.store', $equipment) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Документ</p>
                        <input name="document_name" placeholder="Название документа" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input name="document_number" placeholder="Номер" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input type="date" name="issued_at" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input type="date" name="valid_until" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Добавить</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Поверки</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Дата</th>
                                <th class="px-6 py-4">Действует до</th>
                                <th class="px-6 py-4">Номер</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($equipment->verifications as $verification)
                                <tr>
                                    <td class="px-6 py-5">{{ $verification->verified_at?->format('d.m.Y') }}</td>
                                    <td class="px-6 py-5">{{ $verification->valid_until?->format('d.m.Y') ?: 'Не указано' }}</td>
                                    <td class="px-6 py-5">{{ $verification->certificate_number ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Калибровки</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Дата</th>
                                <th class="px-6 py-4">Действует до</th>
                                <th class="px-6 py-4">Номер</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($equipment->calibrations as $calibration)
                                <tr>
                                    <td class="px-6 py-5">{{ $calibration->calibrated_at?->format('d.m.Y') }}</td>
                                    <td class="px-6 py-5">{{ $calibration->valid_until?->format('d.m.Y') ?: 'Не указано' }}</td>
                                    <td class="px-6 py-5">{{ $calibration->certificate_number ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Выдачи</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Сотрудник</th>
                                <th class="px-6 py-4">Выдано</th>
                                <th class="px-6 py-4">Возврат</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($equipment->assignments as $assignment)
                                <tr>
                                    <td class="px-6 py-5">{{ $assignment->employee?->fullName() }}</td>
                                    <td class="px-6 py-5">{{ $assignment->issued_at?->format('d.m.Y') }}</td>
                                    <td class="px-6 py-5">{{ $assignment->returned_at?->format('d.m.Y') ?: 'Открыта' }}</td>
                                    <td class="px-6 py-5">
                                        @if ($assignment->returned_at === null)
                                            @can('manage', $equipment)
                                                <form method="post" action="{{ route('admin.equipment.assignments.return', [$equipment, $assignment]) }}">
                                                    @csrf
                                                    @method('patch')
                                                    <button type="submit" class="rounded-full border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-700">Вернуть</button>
                                                </form>
                                            @endcan
                                        @else
                                            <span class="text-slate-500">Закрыта</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Перемещения</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Дата</th>
                                <th class="px-6 py-4">Из</th>
                                <th class="px-6 py-4">В</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($equipment->movements as $movement)
                                <tr>
                                    <td class="px-6 py-5">{{ $movement->moved_at?->format('d.m.Y') }}</td>
                                    <td class="px-6 py-5">{{ $movement->fromObject?->name ?: '—' }}</td>
                                    <td class="px-6 py-5">{{ $movement->toObject?->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Дефекты</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Дата</th>
                                <th class="px-6 py-4">Описание</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($equipment->defects as $defect)
                                <tr>
                                    <td class="px-6 py-5">{{ $defect->detected_at?->format('d.m.Y') }}</td>
                                    <td class="px-6 py-5 whitespace-pre-line">{{ $defect->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4 xl:col-span-2">
                <h2 class="text-2xl font-semibold text-slate-900">Документы</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Название</th>
                                <th class="px-6 py-4">Номер</th>
                                <th class="px-6 py-4">Действует до</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($equipment->documents as $document)
                                <tr>
                                    <td class="px-6 py-5">{{ $document->document_name }}</td>
                                    <td class="px-6 py-5">{{ $document->document_number ?: '—' }}</td>
                                    <td class="px-6 py-5">{{ $document->valid_until?->format('d.m.Y') ?: 'Не указано' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
