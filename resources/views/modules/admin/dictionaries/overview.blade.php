@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Администрирование</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Базовые справочники</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Справочники используются для нормализованных значений в объектах, сотрудниках и производственных данных.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($definitions as $key => $definition)
                <a href="{{ route('admin.dictionaries.index', $key) }}" class="panel block p-5 transition hover:border-brand-200 hover:bg-brand-50/40">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $key }}</p>
                    <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $definition['label'] }}</h2>
                </a>
            @endforeach
        </div>
    </div>
@endsection
