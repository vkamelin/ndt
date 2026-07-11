<nav class="space-y-6 text-sm">
    <section class="space-y-2">
        <p class="panel-title px-2">Быстрый доступ</p>
        <div class="flex flex-col">
            <a href="{{ route('home') }}" @click="menuOpen = false" class="nav-link">Главная</a>
            <a href="{{ route('dashboard') }}" @click="menuOpen = false" class="nav-link nav-link-active">Рабочий стол</a>
            @can('notifications.view_own')
                <a href="{{ route('notifications.index') }}" @click="menuOpen = false" class="nav-link">
                    Уведомления
                    <span class="ml-2 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-700">
                        {{ auth()->user()->systemNotifications()->whereNull('read_at')->count() }}
                    </span>
                </a>
            @endcan
            <a href="{{ route('profile.show') }}" @click="menuOpen = false" class="nav-link">Профиль</a>
        </div>
    </section>

    <section class="space-y-2">
        <p class="panel-title px-2">Работа</p>
        <div class="flex flex-col">
            @can('ndt_requests.view_any')
                <a href="{{ route('admin.ndt-requests.index') }}" @click="menuOpen = false" class="nav-link">Заявки НК</a>
            @endcan
            @can('welds.view_any')
                <a href="{{ route('admin.welds.index') }}" @click="menuOpen = false" class="nav-link">Стыки</a>
            @endcan
            @can('ndt_tasks.view_any')
                <a href="{{ route('admin.ndt-tasks.index') }}" @click="menuOpen = false" class="nav-link">Задания НК</a>
            @endcan
            @can('ndt_results.view_any')
                <a href="{{ route('admin.ndt-results.index') }}" @click="menuOpen = false" class="nav-link">Результаты</a>
            @endcan
            @can('conclusions.view_any')
                <a href="{{ route('admin.conclusions.index') }}" @click="menuOpen = false" class="nav-link">Заключения</a>
            @endcan
            @can('radiography.view_any')
                <a href="{{ route('admin.radiography.index') }}" @click="menuOpen = false" class="nav-link">РК</a>
            @endcan
            @can('shifts.view_any')
                <a href="{{ route('admin.shifts.index') }}" @click="menuOpen = false" class="nav-link">Смены</a>
            @endcan
            @can('equipment.view_any')
                <a href="{{ route('admin.equipment.index') }}" @click="menuOpen = false" class="nav-link">Оборудование</a>
            @endcan
            @can('document.view_any')
                <a href="{{ route('admin.documents.index') }}" @click="menuOpen = false" class="nav-link">Документы</a>
            @endcan
            @can('reports.view_any')
                <a href="{{ route('admin.reports.index') }}" @click="menuOpen = false" class="nav-link">Отчеты</a>
            @endcan
        </div>
    </section>

    <section class="space-y-2">
        <p class="panel-title px-2">Администрирование</p>
        <div class="flex flex-col">
            @can('users.view')
                <a href="{{ route('admin.users.index') }}" @click="menuOpen = false" class="nav-link">Пользователи</a>
            @endcan
            @can('employees.view_any')
                <a href="{{ route('admin.employees.index') }}" @click="menuOpen = false" class="nav-link">Сотрудники</a>
            @endcan
            @can('cities.view_any')
                <a href="{{ route('admin.cities.index') }}" @click="menuOpen = false" class="nav-link">Города</a>
            @endcan
            @can('objects.view_any')
                <a href="{{ route('admin.objects.index') }}" @click="menuOpen = false" class="nav-link">Объекты</a>
            @endcan
            @can('organizations.view_any')
                <a href="{{ route('admin.organizations.index') }}" @click="menuOpen = false" class="nav-link">Организации</a>
            @endcan
            @can('positions.manage')
                <a href="{{ route('admin.positions.index') }}" @click="menuOpen = false" class="nav-link">Должности</a>
            @endcan
            @can('directories.manage')
                <a href="{{ route('admin.dictionaries.overview') }}" @click="menuOpen = false" class="nav-link">Справочники</a>
            @endcan
            @can('viewAny', \App\Modules\Audit\Models\AuditLog::class)
                <a href="{{ route('admin.audit-logs.index') }}" @click="menuOpen = false" class="nav-link">Журнал аудита</a>
            @endcan
            <form method="post" action="{{ route('logout') }}" class="pt-1">
                @csrf
                <button type="submit" class="nav-link w-full text-left">Выйти</button>
            </form>
        </div>
    </section>
</nav>
