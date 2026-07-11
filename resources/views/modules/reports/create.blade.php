@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Отчеты</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Постановка отчета</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма постановки отчета в очередь вынесена со списка.
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

        @can('create', \App\Modules\Reports\Models\ReportJob::class)
            <form method="post" action="{{ route('admin.reports.store') }}" class="panel p-6 space-y-6">
                @csrf
                <div class="grid gap-4 md:grid-cols-4">
                    @if ($isAdmin)
                        <div class="space-y-2">
                            <label for="queue_city_id" class="text-sm font-medium text-slate-700">Город</label>
                            <select id="queue_city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Без фильтра</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}" @selected((string) old('city_id') === (string) $city->id)>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="queue_object_id" class="text-sm font-medium text-slate-700">Объект/участок</label>
                            <select id="queue_object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                                <option value="">Без фильтра</option>
                                @foreach ($objects as $object)
                                    <option value="{{ $object->id }}" @selected((string) old('object_id') === (string) $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="city_id" value="{{ $scopeCity?->id }}">
                        <input type="hidden" name="object_id" value="{{ $scopeObject?->id }}">
                        <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            <p class="font-medium text-slate-900">Контекст заявки</p>
                            <p class="mt-1">{{ $scopeObject?->name }} @if ($scopeCity) · {{ $scopeCity->name }} @endif</p>
                        </div>
                    @endif
                    <div class="space-y-2">
                        <label for="queue_search" class="text-sm font-medium text-slate-700">Поиск</label>
                        <input id="queue_search" name="search" value="{{ old('search') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label for="queue_status" class="text-sm font-medium text-slate-700">Статус</label>
                        <select id="queue_status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <option value="">Без фильтра</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="queue_date_from" class="text-sm font-medium text-slate-700">С даты</label>
                        <input id="queue_date_from" type="date" name="date_from" value="{{ old('date_from') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label for="queue_date_to" class="text-sm font-medium text-slate-700">По дату</label>
                        <input id="queue_date_to" type="date" name="date_to" value="{{ old('date_to') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="flex items-end gap-3 md:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="include_defects" value="1" @checked(old('include_defects'))>
                            Включить дефекты
                        </label>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($reportTypes as $reportType)
                        <button type="submit" name="report_type" value="{{ $reportType->value }}" class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-brand-300 hover:bg-brand-50">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $reportType->format()->label() }}</p>
                                    <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ $reportType->label() }}</h2>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $reportType->format()->label() }}</span>
                            </div>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $reportType->description() }}</p>
                        </button>
                    @endforeach
                </div>
            </form>
        @endcan
    </div>
@endsection
