<div class="space-y-6">
    <div class="panel p-5">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Сущность</span>
                <select wire:model.live="entityType" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    <option value="">Все</option>
                    @foreach ($entityTypes as $entityType)
                        <option value="{{ $entityType }}">{{ class_basename($entityType) }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Пользователь</span>
                <select wire:model.live="actorUserId" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    <option value="">Все</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Операция</span>
                <select wire:model.live="operation" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
                    <option value="">Все</option>
                    @foreach ($operations as $operationName)
                        <option value="{{ $operationName }}">{{ $operationName }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Дата с</span>
                <input wire:model.live="fromDate" type="date" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
            </label>

            <label class="block">
                <span class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Дата по</span>
                <input wire:model.live="toDate" type="date" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-brand-400 focus:ring-4 focus:ring-brand-100">
            </label>
        </div>
    </div>

    <div class="panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-[0.2em] text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Когда</th>
                        <th class="px-6 py-4">Кто</th>
                        <th class="px-6 py-4">Сущность</th>
                        <th class="px-6 py-4">Операция</th>
                        <th class="px-6 py-4">Изменения</th>
                        <th class="px-6 py-4">Причина</th>
                        <th class="px-6 py-4">IP / User-Agent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($logs as $log)
                        <tr class="align-top">
                            <td class="px-6 py-5 whitespace-nowrap text-slate-600">
                                {{ $log->created_at?->format('d.m.Y H:i:s') }}
                            </td>
                            <td class="px-6 py-5">
                                <p class="font-medium text-slate-900">{{ $log->actor?->name ?? 'Система' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $log->actor?->email ?? 'Без пользователя' }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <p class="font-medium text-slate-900">{{ class_basename($log->subject_type) }}</p>
                                <p class="mt-1 text-xs text-slate-500">ID: {{ $log->subject_id }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $log->event }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <details class="group">
                                    <summary class="cursor-pointer text-sm font-medium text-brand-700">Показать</summary>
                                    <div class="mt-3 space-y-3 text-xs text-slate-600">
                                        <div>
                                            <p class="font-semibold uppercase tracking-[0.2em] text-slate-500">Было</p>
                                            <pre class="mt-1 overflow-x-auto rounded-2xl bg-slate-50 p-3">{{ json_encode($log->properties['before'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        <div>
                                            <p class="font-semibold uppercase tracking-[0.2em] text-slate-500">Стало</p>
                                            <pre class="mt-1 overflow-x-auto rounded-2xl bg-slate-50 p-3">{{ json_encode($log->properties['after'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                </details>
                            </td>
                            <td class="px-6 py-5 text-slate-600">
                                {{ $log->reason ?: '—' }}
                            </td>
                            <td class="px-6 py-5 text-slate-600">
                                <p>{{ $log->ip_address ?: '—' }}</p>
                                <p class="mt-1 max-w-xs break-words text-xs text-slate-500">{{ $log->user_agent ?: '—' }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-slate-500">
                                Записи аудита не найдены.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $logs->links() }}
    </div>
</div>
