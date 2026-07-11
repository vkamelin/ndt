@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Организации</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $organization->name }} · Редактирование</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Форма редактирования вынесена отдельно от карточки.
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
            <form method="post" action="{{ route('admin.organizations.update', $organization) }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('patch')
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700" for="name">Наименование организации</label>
                    <input id="name" name="name" value="{{ old('name', $organization->name) }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">
                </div>
                <div class="space-y-2 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700" for="comment">Комментарий</label>
                    <textarea id="comment" name="comment" rows="2" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm">{{ old('comment', $organization->comment) }}</textarea>
                </div>
                <label class="flex items-center gap-3 text-sm text-slate-600 md:col-span-2">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-100" @checked(old('is_active', $organization->is_active))>
                    Активно
                </label>
                <div class="md:col-span-2 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">Сохранить организацию</button>
                    <a href="{{ route('admin.organizations.show', $organization) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection
