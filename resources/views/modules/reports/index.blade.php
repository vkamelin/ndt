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
            <div class="panel p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-slate-900">Постановка отчета</h2>
                        <p class="mt-2 text-sm text-slate-600">Большая форма перенесена на отдельную страницу.</p>
                    </div>
                    <a href="{{ route('admin.reports.create') }}" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Создать отчет</a>
                </div>
            </div>
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
