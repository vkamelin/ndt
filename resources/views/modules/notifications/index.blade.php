@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="panel-title">Уведомления</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900">Внутренние сообщения системы</h1>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                        Здесь отображаются персональные уведомления по заданиям, сменам и системным предупреждениям.
                    </p>
                </div>
                <form method="post" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        Отметить все прочитанными
                    </button>
                </form>
            </div>
            <p class="mt-4 text-sm font-medium text-slate-700">
                Непрочитанных уведомлений: {{ $unreadCount }}
            </p>
        </div>

        <div class="panel overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-6 py-3 font-medium">Уведомление</th>
                            <th class="px-6 py-3 font-medium">Тип</th>
                            <th class="px-6 py-3 font-medium">Статус</th>
                            <th class="px-6 py-3 font-medium">Дата</th>
                            <th class="px-6 py-3 font-medium">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($notifications as $notification)
                            <tr class="{{ $notification->read_at ? '' : 'bg-amber-50/40' }}">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                                    <p class="mt-1 text-slate-600">{{ $notification->body }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-700">{{ $notification->type->label() }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $notification->read_at ? 'bg-slate-200 text-slate-700' : 'bg-brand-50 text-brand-700' }}">
                                        {{ $notification->read_at ? 'Прочитано' : 'Новое' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $notification->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    @if ($notification->read_at === null)
                                        <form method="post" action="{{ route('notifications.read', $notification) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-brand-700 hover:text-brand-800">
                                                Отметить прочитанным
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                    Уведомлений пока нет.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection
