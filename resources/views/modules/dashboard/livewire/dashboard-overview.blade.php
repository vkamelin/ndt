<div class="space-y-6">
    <div class="panel p-6">
        <p class="panel-title">Рабочий стол</p>
        <h1 class="mt-2 text-3xl font-semibold text-slate-900">Оперативная сводка по НК</h1>
        <p class="mt-3 max-w-4xl text-sm leading-7 text-slate-600">
            Сводка показывает актуальные задания, заявки, смены, уведомления и предупреждения
            по объекту/участку или по всей системе для администратора.
        </p>
        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-slate-500">Пользователь</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">{{ $summary['user']->name }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Роли</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">
                    {{ $summary['role_names'] !== [] ? implode(', ', $summary['role_names']) : 'Без роли' }}
                </p>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Статус</p>
                <p class="mt-1 text-lg font-semibold text-slate-900">{{ $summary['user']->status->label() }}</p>
            </div>
        </div>
        <p class="mt-4 text-sm font-medium text-slate-700">
            Вы вошли как {{ $summary['user']->name }}.
        </p>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="panel p-5">
            <p class="text-sm font-medium text-slate-500">Активные заявки</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['active_requests']->count() }}</p>
        </div>
        <div class="panel p-5">
            <p class="text-sm font-medium text-slate-500">Просроченные задания</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['overdue_tasks']->count() }}</p>
        </div>
        <div class="panel p-5">
            <p class="text-sm font-medium text-slate-500">Мои задания</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['my_tasks']->count() }}</p>
        </div>
        <div class="panel p-5">
            <p class="text-sm font-medium text-slate-500">Непрочитанные уведомления</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['unread_notifications_count'] }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="panel p-6 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="panel-title">Заявки</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">На утверждении</h2>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $summary['approval_requests']->count() }}</span>
            </div>
            <div class="space-y-3 text-sm">
                @forelse ($summary['approval_requests'] as $request)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $request->request_number }}</p>
                        <p class="mt-1 text-slate-600">{{ $request->object?->city?->name }} · {{ $request->object?->name }}</p>
                        <p class="mt-1 text-slate-500">{{ $request->status->label() }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">Нет заявок на утверждении.</p>
                @endforelse
            </div>
        </div>

        <div class="panel p-6 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="panel-title">Смены</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Незавершенные</h2>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $summary['open_shifts']->count() }}</span>
            </div>
            <div class="space-y-3 text-sm">
                @forelse ($summary['open_shifts'] as $shift)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $shift->employee?->fullName() }}</p>
                        <p class="mt-1 text-slate-600">{{ $shift->object?->city?->name }} · {{ $shift->object?->name }}</p>
                        <p class="mt-1 text-slate-500">{{ $shift->type->label() }} · {{ $shift->status->label() }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">Нет незавершенных смен.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="panel p-6 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="panel-title">Задания</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Просроченные</h2>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $summary['overdue_tasks']->count() }}</span>
            </div>
            <div class="space-y-3 text-sm">
                @forelse ($summary['overdue_tasks'] as $task)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $task->task_number }}</p>
                        <p class="mt-1 text-slate-600">{{ $task->object?->city?->name }} · {{ $task->object?->name }}</p>
                        <p class="mt-1 text-slate-500">{{ $task->method?->label() }} · {{ $task->planned_date?->format('d.m.Y') }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">Нет просроченных заданий.</p>
                @endforelse
            </div>
        </div>

        <div class="panel p-6 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="panel-title">Уведомления</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Последние</h2>
                </div>
                <a href="{{ route('notifications.index') }}" class="text-sm font-semibold text-brand-700">Все</a>
            </div>
            <div class="space-y-3 text-sm">
                @forelse ($summary['latest_notifications'] as $notification)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <p class="font-medium text-slate-900">{{ $notification->title }}</p>
                            <span class="rounded-full px-2 py-1 text-[11px] font-semibold {{ $notification->read_at ? 'bg-slate-200 text-slate-700' : 'bg-brand-50 text-brand-700' }}">
                                {{ $notification->read_at ? 'Прочитано' : 'Новое' }}
                            </span>
                        </div>
                        <p class="mt-1 text-slate-600">{{ $notification->body }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">Уведомлений пока нет.</p>
                @endforelse
            </div>
        </div>

        <div class="panel p-6 space-y-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="panel-title">Предупреждения</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-900">Оборудование и квалификации</h2>
                </div>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">{{ $summary['expiring_verifications']->count() + $summary['expiring_calibrations']->count() + $summary['expiring_qualifications']->count() }}</span>
            </div>
            <p class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                Строгий контроль квалификаций: {{ $summary['strict_qualification_guard'] ? 'включен' : 'выключен' }}.
            </p>

            <div class="space-y-3 text-sm">
                @forelse ($summary['expiring_verifications'] as $verification)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $verification->equipment?->name }}</p>
                        <p class="mt-1 text-slate-600">{{ $verification->equipment?->object?->city?->name }} · {{ $verification->equipment?->object?->name }}</p>
                        <p class="mt-1 text-slate-500">Поверка до {{ $verification->valid_until?->format('d.m.Y') }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">Нет истекающих поверок.</p>
                @endforelse

                @forelse ($summary['expiring_calibrations'] as $calibration)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $calibration->equipment?->name }}</p>
                        <p class="mt-1 text-slate-600">{{ $calibration->equipment?->object?->city?->name }} · {{ $calibration->equipment?->object?->name }}</p>
                        <p class="mt-1 text-slate-500">Калибровка до {{ $calibration->valid_until?->format('d.m.Y') }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">Нет истекающих калибровок.</p>
                @endforelse

                @forelse ($summary['expiring_qualifications'] as $qualification)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-medium text-slate-900">{{ $qualification->employee?->fullName() }}</p>
                        <p class="mt-1 text-slate-600">{{ $qualification->employee?->object?->city?->name }} · {{ $qualification->employee?->object?->name }}</p>
                        <p class="mt-1 text-slate-500">{{ $qualification->method?->label() }} до {{ $qualification->valid_until?->format('d.m.Y') }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">Нет истекающих удостоверений.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
