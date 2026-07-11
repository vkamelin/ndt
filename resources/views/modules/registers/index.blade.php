@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Реестры</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Реестры передачи, акты и архив</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Раздел для передачи материалов, фиксации актов и архивных связок с заключениями, пленками и файлами.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.registers.index') }}" class="grid gap-4 md:grid-cols-4">
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700" for="search">Поиск</label>
                    <input id="search" name="search" value="{{ request('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="register_type_id">Тип</label>
                    <select id="register_type_id" name="register_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($registerTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) request('register_type_id') === (string) $type->id)>{{ $type->name }}</option>
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
                <div class="flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Применить</button>
                </div>
            </form>
        </div>

        @can('registers.manage')
            <div class="panel p-6">
                <form method="post" action="{{ route('admin.registers.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="register_type_id_create">Тип реестра</label>
                        <select id="register_type_id_create" name="register_type_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($registerTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                        <input id="number" name="number" value="{{ old('number') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="date">Дата</label>
                        <input id="date" type="date" name="date" value="{{ old('date', today()->toDateString()) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    @if ($isAdmin)
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="city_id">Город</label>
                            <select id="city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="object_id">Объект/участок</label>
                            <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}">{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="city_id" value="{{ $scopeCity?->id }}">
                        <input type="hidden" name="object_id" value="{{ $scopeObject?->id }}">
                        <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <p class="font-medium text-slate-900">Контекст реестра</p>
                            <p class="mt-1">{{ $scopeObject?->name }} @if ($scopeCity) · {{ $scopeCity->name }} @endif</p>
                        </div>
                    @endif
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="sender_employee_id">Отправитель</label>
                        <select id="sender_employee_id" name="sender_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->fullName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="receiver_employee_id">Получатель</label>
                        <select id="receiver_employee_id" name="receiver_employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->fullName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="status_create">Статус</label>
                        <select id="status_create" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'draft')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3 space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                        <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment') }}</textarea>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Создать реестр</button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Реестр</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Позиции</th>
                            <th class="px-6 py-4">Файлы</th>
                            <th class="px-6 py-4">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($registers as $register)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $register->type?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $register->number }}</p>
                                    <p class="mt-1 text-slate-500">{{ $register->date?->format('d.m.Y') }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $register->object?->name }}</p>
                                    <p class="mt-1 text-slate-500">{{ $register->city?->name }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $register->status->label() }}</td>
                                <td class="px-6 py-5">{{ $register->items_count }}</td>
                                <td class="px-6 py-5">{{ $register->files_count }}</td>
                                <td class="px-6 py-5">
                                    <a href="{{ route('admin.registers.show', $register) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700">Открыть</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Реестры не найдены.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $registers->links() }}
    </div>
@endsection
