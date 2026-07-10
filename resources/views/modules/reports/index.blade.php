@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Отчеты</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Отчеты, экспорты и печатные формы</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Раздел для постановки тяжелых экспортов в очередь и отслеживания статуса готовности файлов.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="panel p-6">
            <form method="get" action="{{ route('admin.reports.index') }}" class="grid gap-4 md:grid-cols-4">
                <div class="space-y-2">
                    <label for="status" class="text-sm font-medium text-slate-700">Статус</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="report_type_filter" class="text-sm font-medium text-slate-700">Тип</label>
                    <select id="report_type_filter" name="report_type" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($reportTypes as $reportType)
                            <option value="{{ $reportType->value }}" @selected(request('report_type') === $reportType->value)>{{ $reportType->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="city_id" class="text-sm font-medium text-slate-700">Город</label>
                    <select id="city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) request('city_id') === (string) $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="object_id" class="text-sm font-medium text-slate-700">Объект/участок</label>
                    <select id="object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                        <option value="">Все</option>
                        @foreach ($objects as $object)
                            <option value="{{ $object->id }}" @selected((string) request('object_id') === (string) $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex items-end">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Применить</button>
                </div>
            </form>
        </div>

        @can('create', \App\Modules\Reports\Models\ReportJob::class)
            <form method="post" action="{{ route('admin.reports.store') }}" class="panel p-6 space-y-6">
                @csrf
                <div class="grid gap-4 md:grid-cols-4">
                    <div class="space-y-2">
                        <label for="queue_city_id" class="text-sm font-medium text-slate-700">Город</label>
                        <select id="queue_city_id" name="city_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <option value="">Без фильтра</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected((string) old('city_id', request('city_id')) === (string) $city->id)>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="queue_object_id" class="text-sm font-medium text-slate-700">Объект/участок</label>
                        <select id="queue_object_id" name="object_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <option value="">Без фильтра</option>
                            @foreach ($objects as $object)
                                <option value="{{ $object->id }}" @selected((string) old('object_id', request('object_id')) === (string) $object->id)>{{ $object->name }} @if ($object->city) ({{ $object->city->name }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="queue_search" class="text-sm font-medium text-slate-700">Поиск</label>
                        <input id="queue_search" name="search" value="{{ old('search', request('search')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label for="queue_status" class="text-sm font-medium text-slate-700">Статус</label>
                        <select id="queue_status" name="status" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                            <option value="">Без фильтра</option>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', request('status')) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="queue_date_from" class="text-sm font-medium text-slate-700">С даты</label>
                        <input id="queue_date_from" type="date" name="date_from" value="{{ old('date_from', request('date_from')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="space-y-2">
                        <label for="queue_date_to" class="text-sm font-medium text-slate-700">По дату</label>
                        <input id="queue_date_to" type="date" name="date_to" value="{{ old('date_to', request('date_to')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                    </div>
                    <div class="flex items-end gap-3 md:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" name="include_defects" value="1" @checked(old('include_defects', request('include_defects')))>
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

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Отчет</th>
                            <th class="px-6 py-4">Объект</th>
                            <th class="px-6 py-4">Статус</th>
                            <th class="px-6 py-4">Файл</th>
                            <th class="px-6 py-4">Очередь</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($reportJobs as $reportJob)
                            <tr>
                                <td class="px-6 py-5">
                                    <p class="font-medium text-slate-900">{{ $reportJob->title }}</p>
                                    <p class="mt-1 text-slate-500">{{ $reportJob->report_type->label() }}</p>
                                </td>
                                <td class="px-6 py-5">
                                    <p class="text-slate-900">{{ $reportJob->object?->name ?: 'Без объекта' }}</p>
                                    <p class="mt-1 text-slate-500">{{ $reportJob->city?->name ?: 'Без города' }}</p>
                                </td>
                                <td class="px-6 py-5">{{ $reportJob->status->label() }}</td>
                                <td class="px-6 py-5">
                                    @if ($reportJob->file)
                                        <a href="{{ route('admin.files.download', $reportJob->file) }}" class="rounded-full bg-brand-50 px-4 py-2 text-sm font-medium text-brand-700">Скачать</a>
                                    @else
                                        <span class="text-slate-500">Пока не готов</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-slate-600">
                                    <p>{{ $reportJob->queued_at?->format('d.m.Y H:i') }}</p>
                                    @if ($reportJob->finished_at)
                                        <p class="mt-1">{{ $reportJob->finished_at->format('d.m.Y H:i') }}</p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Отчеты еще не создавались.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $reportJobs->links() }}
    </div>
@endsection
