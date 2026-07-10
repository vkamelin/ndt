@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Реестр</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $register->type?->name }} {{ $register->number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Карточка реестра с позициями, актами, архивными делами и файлами.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.4fr,1fr]">
            <div class="panel p-6 space-y-6">
                @can('manage', $register)
                    <form method="post" action="{{ route('admin.registers.update', $register) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @csrf
                        @method('patch')
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="register_type_id">Тип реестра</label>
                            <select id="register_type_id" name="register_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($registerTypes as $type)
                                    <option value="{{ $type->id }}" @selected(old('register_type_id', $register->register_type_id) == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                            <input id="number" name="number" value="{{ old('number', $register->number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="date">Дата</label>
                            <input id="date" type="date" name="date" value="{{ old('date', optional($register->date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="city_id">Город</label>
                            <select id="city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @selected(old('city_id', $register->city_id) == $city->id)>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected(old('object_id', $register->object_id) == $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="sender_employee_id">Отправитель</label>
                            <select id="sender_employee_id" name="sender_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('sender_employee_id', $register->sender_employee_id) == $employee->id)>{{ $employee->fullName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="receiver_employee_id">Получатель</label>
                            <select id="receiver_employee_id" name="receiver_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('receiver_employee_id', $register->receiver_employee_id) == $employee->id)>{{ $employee->fullName() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="status">Статус</label>
                            <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $register->status->value) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3 space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                            <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment', $register->comment) }}</textarea>
                        </div>
                        <div class="md:col-span-2 xl:col-span-3">
                            <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                        </div>
                    </form>
                @endcan

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Статус</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $register->status->label() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Объект/участок</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $register->object?->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $register->city?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Отправитель</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $register->senderEmployee?->fullName() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Получатель</p>
                        <p class="mt-2 text-sm font-medium text-slate-900">{{ $register->receiverEmployee?->fullName() }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 md:col-span-2 xl:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Печать</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <form method="post" action="{{ route('admin.reports.store') }}">
                                @csrf
                                <input type="hidden" name="report_type" value="registers">
                                <input type="hidden" name="entity_id" value="{{ $register->id }}">
                                <button type="submit" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">PDF реестра через отчеты</button>
                            </form>
                        </div>
                    </div>
                </div>

                @can('transition', $register)
                    <div class="flex flex-wrap gap-2">
                        @foreach (['formed', 'sent', 'accepted', 'returned', 'closed'] as $status)
                            <form method="post" action="{{ route('admin.registers.status.update', $register) }}">
                                @csrf
                                @method('patch')
                                <input type="hidden" name="status" value="{{ $status }}">
                                <button type="submit" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700">{{ $statuses[$status] }}</button>
                            </form>
                        @endforeach
                    </div>
                @endcan

                <div class="flex flex-wrap gap-2">
                    <form method="post" action="{{ route('admin.registers.export.pdf', $register) }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700">PDF</button>
                    </form>
                    <form method="post" action="{{ route('admin.registers.export.excel', $register) }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700">Excel</button>
                    </form>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                @can('manage', $register)
                    <form method="post" action="{{ route('admin.registers.files.store', $register) }}" enctype="multipart/form-data" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Загрузить файл к реестру</p>
                        <input type="file" name="file" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Загрузить</button>
                    </form>
                @endcan

                @can('manage', $register)
                    <form method="post" action="{{ route('admin.registers.items.store', $register) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Добавить позицию</p>
                        <input name="related_type" placeholder="App\\Modules\\..." class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input name="related_id" type="number" placeholder="ID" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input name="sort_order" type="number" value="1" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Добавить</button>
                    </form>
                @endcan

                @can('act', $register)
                    <form method="post" action="{{ route('admin.registers.acts.store', $register) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Создать акт</p>
                        <select name="act_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($actTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <input name="number" placeholder="Номер" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input name="date" type="date" value="{{ today()->toDateString() }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <select name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <select name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}">{{ $object->name }}</option>
                            @endforeach
                        </select>
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Создать</button>
                    </form>
                @endcan

                @can('archive', $register)
                    <form method="post" action="{{ route('admin.registers.archive-cases.store', $register) }}" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="text-sm font-medium text-slate-900">Создать архивное дело</p>
                        <input name="number" placeholder="Номер" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <input name="date" type="date" value="{{ today()->toDateString() }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <select name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                        <select name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}">{{ $object->name }}</option>
                            @endforeach
                        </select>
                        <textarea name="comment" rows="2" placeholder="Комментарий" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm"></textarea>
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Создать</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Позиции</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">№</th>
                                <th class="px-6 py-4">Сущность</th>
                                <th class="px-6 py-4">Файл</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($register->items as $item)
                                <tr>
                                    <td class="px-6 py-5">{{ $item->sort_order }}</td>
                                    <td class="px-6 py-5">{{ class_basename($item->related_type) }} #{{ $item->related_id }}</td>
                                    <td class="px-6 py-5">{{ $item->file?->original_name ?: 'Нет' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-500">Позиции отсутствуют.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Акты и архив</h2>
                <div class="space-y-4">
                    @forelse ($register->acts as $act)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">{{ $act->type?->name }} {{ $act->number }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $act->date?->format('d.m.Y') }} · {{ $act->object?->name }}</p>
                            @can('manage', $act)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form method="post" action="{{ route('admin.registers.acts.export.pdf', $act) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700">PDF</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.registers.acts.export.excel', $act) }}">
                                        @csrf
                                        <button type="submit" class="rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700">Excel</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.reports.store') }}">
                                        @csrf
                                        <input type="hidden" name="report_type" value="acts">
                                        <input type="hidden" name="entity_id" value="{{ $act->id }}">
                                        <button type="submit" class="rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-700">Через отчеты</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Акты отсутствуют.</p>
                    @endforelse

                    @forelse ($register->archiveCases as $archiveCase)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="font-medium text-slate-900">{{ $archiveCase->number }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $archiveCase->date?->format('d.m.Y') }} · {{ $archiveCase->object?->name }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Архивные дела отсутствуют.</p>
                    @endforelse
                </div>
            </div>

            <div class="panel p-6 space-y-4 xl:col-span-2">
                <h2 class="text-2xl font-semibold text-slate-900">Файлы</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Имя</th>
                                <th class="px-6 py-4">Загрузил</th>
                                <th class="px-6 py-4">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($register->files as $file)
                                <tr>
                                    <td class="px-6 py-5">{{ $file->original_name }}</td>
                                    <td class="px-6 py-5">{{ $file->uploadedBy?->name ?: 'Система' }}</td>
                                    <td class="px-6 py-5">
                                        <a href="{{ route('admin.files.download', $file) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700">Скачать</a>
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
        </div>
    </div>
@endsection
