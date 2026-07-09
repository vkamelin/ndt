@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Стыки</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Сварные стыки и сварщики</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточки стыков, привязка к объекту/участку, и справочник сварщиков с клеймами.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.welds.index') }}" class="grid gap-4 md:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск по номеру</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Все статусы</option>
                        @foreach (\App\Modules\Welds\Enums\WeldStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="object_id_filter">Объект/участок</label>
                    <select id="object_id_filter" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        <option value="">Все объекты</option>
                        @foreach ($objects as $object)
                            <option value="{{ $object->id }}" @selected(request('object_id') == $object->id)>{{ $object->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Применить фильтры</button>
                </div>
            </form>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            @can('welds.manage')
                <div class="panel p-6">
                    <h2 class="text-2xl font-semibold text-slate-900">Новый стык</h2>
                    <form method="post" action="{{ route('admin.welds.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected(old('object_id') == $object->id)>{{ $object->name }} ({{ $object->city?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="weld_number">Номер стыка</label>
                            <input id="weld_number" name="weld_number" value="{{ old('weld_number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
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
                            <label class="text-sm font-medium text-slate-700" for="drawing_id">Чертеж</label>
                            <select id="drawing_id" name="drawing_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($drawings as $drawing)
                                    <option value="{{ $drawing->id }}" @selected(old('drawing_id') == $drawing->id)>{{ $drawing->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="line_id">Линия</label>
                            <select id="line_id" name="line_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($lines as $line)
                                    <option value="{{ $line->id }}" @selected(old('line_id') == $line->id)>{{ $line->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="diameter">Диаметр</label>
                            <input id="diameter" name="diameter" value="{{ old('diameter') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="thickness">Толщина</label>
                            <input id="thickness" name="thickness" value="{{ old('thickness') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="material_1_id">Материал 1</label>
                            <select id="material_1_id" name="material_1_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}" @selected(old('material_1_id') == $material->id)>{{ $material->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="material_2_id">Материал 2</label>
                            <select id="material_2_id" name="material_2_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не выбран</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}" @selected(old('material_2_id') == $material->id)>{{ $material->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="welded_at">Дата сварки</label>
                            <input id="welded_at" type="date" name="welded_at" value="{{ old('welded_at') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить стык</button>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="panel p-6">
                <h2 class="text-2xl font-semibold text-slate-900">Сварщики</h2>
                @can('welders.manage')
                    <form method="post" action="{{ route('admin.welders.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                        @csrf
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="welder_name">Имя сварщика</label>
                            <input id="welder_name" name="name" value="{{ old('name') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="employee_id">Сотрудник</label>
                            <select id="employee_id" name="employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                <option value="">Не привязан</option>
                                @foreach ($employeeOptions as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->fullName() }} ({{ $employee->object?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="stamp">Клеймо</label>
                            <input id="stamp" name="stamp" value="{{ old('stamp') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700" for="welder_comment">Комментарий</label>
                            <input id="welder_comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                        </div>
                        <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-2">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', true))>
                            Активен
                        </label>
                        <div class="md:col-span-2">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Добавить сварщика</button>
                        </div>
                    </form>
                @endcan

                @can('welders.view_any')
                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                                <tr>
                                    <th class="px-6 py-4">Имя</th>
                                    <th class="px-6 py-4">Клеймо</th>
                                    <th class="px-6 py-4">Сотрудник</th>
                                    <th class="px-6 py-4">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach ($welders as $welder)
                                    <tr>
                                        <td class="px-6 py-5">
                                            @can('welders.manage')
                                            <form method="post" action="{{ route('admin.welders.update', $welder) }}" class="space-y-3">
                                                @csrf
                                                @method('patch')
                                                <input name="name" value="{{ old('name', $welder->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            @else
                                                <p class="font-medium text-slate-900">{{ $welder->name }}</p>
                                                <p class="mt-1 text-slate-500">{{ $welder->comment ?: 'Без комментария' }}</p>
                                            @endcan
                                        </td>
                                        <td class="px-6 py-5">
                                            @can('welders.manage')
                                            <input name="stamp" value="{{ old('stamp', $welder->stamp) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            @else
                                                <p class="font-medium text-slate-900">{{ $welder->stamp }}</p>
                                            @endcan
                                        </td>
                                        <td class="px-6 py-5">
                                            @can('welders.manage')
                                            <select name="employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                                <option value="">Не привязан</option>
                                                @foreach ($employeeOptions as $employee)
                                                    <option value="{{ $employee->id }}" @selected(old('employee_id', $welder->employee_id) == $employee->id)>{{ $employee->fullName() }}</option>
                                                @endforeach
                                            </select>
                                            <input name="comment" value="{{ old('comment', $welder->comment) }}" class="mt-3 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                                            @else
                                                <p class="font-medium text-slate-900">{{ $welder->employee?->fullName() ?: 'Не привязан' }}</p>
                                            @endcan
                                        </td>
                                        <td class="px-6 py-5">
                                            @can('welders.manage')
                                            <div class="flex flex-wrap gap-2">
                                                <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить</button>
                                            </form>
                                                <form method="post" action="{{ route('admin.welders.destroy', $welder) }}">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="rounded-full border border-rose-200 px-4 py-2 text-sm font-medium text-rose-700 transition hover:bg-rose-50">Деактивировать</button>
                                                </form>
                                            </div>
                                            @else
                                                <span class="text-slate-500">Только просмотр</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endcan
            </div>
        </div>

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Стык</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Карта</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Сварщики</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($welds as $weld)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $weld->weld_number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $weld->title?->name ?: 'Без титула' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $weld->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $weld->object?->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $weld->drawing?->name ?: '—' }}</p>
                                    <p class="mt-1 text-slate-500">{{ $weld->line?->name ?: '—' }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $weld->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-5">
                                    {{ $weld->welders->count() }}
                                </td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.welds.show', $weld) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700 transition hover:bg-brand-100">
                                        Открыть
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $welds->links() }}
    </div>
@endsection
