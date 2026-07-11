@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Смены</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Открытие смены</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма открытия смены вынесена со списка.
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
            <form method="post" action="{{ route('admin.shifts.store') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="employee_id">Сотрудник</label>
                    <select id="employee_id" name="employee_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->fullName() }} — {{ $employee->object?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="type">Тип смены</label>
                    <select id="type" name="type" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <input id="comment" name="comment" value="{{ old('comment') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Открыть смену</button>
                    <a href="{{ route('admin.shifts.index') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
