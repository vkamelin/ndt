@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="panel p-6">
            <p class="panel-title">Администрирование</p>
            <h1 class="mt-2 text-3xl font-semibold text-slate-900">Журнал аудита</h1>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-600">
                Журнал фиксирует критичные изменения: кто, когда, какую сущность изменил,
                какие были старые и новые значения, а также IP, user-agent и причину изменения.
            </p>
        </div>

        @livewire(\App\Modules\Audit\Livewire\AuditLogList::class)
    </div>
@endsection
