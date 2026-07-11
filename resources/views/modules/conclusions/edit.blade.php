@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Заключение</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $conclusion->number }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Отдельная форма редактирования заключения.
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
            <form method="post" action="{{ route('admin.conclusions.update', $conclusion) }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @csrf
                @method('patch')
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="number">Номер</label>
                    <input id="number" name="number" value="{{ old('number', $conclusion->number) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="date">Дата</label>
                    <input id="date" type="date" name="date" value="{{ old('date', optional($conclusion->date)->format('Y-m-d')) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="xl:col-span-3 space-y-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment', $conclusion->comment) }}</textarea>
                </div>
                <div class="xl:col-span-3 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white">Сохранить</button>
                    <a href="{{ route('admin.conclusions.show', $conclusion) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.45fr,1fr]">
            <div class="panel p-6 space-y-4">
                <h2 class="text-2xl font-semibold text-slate-900">Позиции</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">№</th>
                                <th class="px-6 py-4">Стык</th>
                                <th class="px-6 py-4">Метод</th>
                                <th class="px-6 py-4">Результат</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @foreach ($conclusion->items as $item)
                                <tr>
                                    <td class="px-6 py-5">{{ $item->sort_order }}</td>
                                    <td class="px-6 py-5">{{ $item->result?->weld?->weld_number ?: 'Не указан' }}</td>
                                    <td class="px-6 py-5">{{ $item->result?->method?->name ?: 'Не указан' }}</td>
                                    <td class="px-6 py-5">{{ $item->result?->status->label() ?: 'Не указан' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel p-6 space-y-3">
                <h2 class="text-2xl font-semibold text-slate-900">Результаты</h2>
                <p class="text-sm text-slate-600">Список доступных результатов остается на отдельной странице создания и в основной карточке.</p>
                <div class="space-y-2">
                    @foreach ($readyResults as $result)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                            {{ $result->weld?->weld_number }} · {{ $result->method?->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
