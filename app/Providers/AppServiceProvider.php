<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Audit\Policies\AuditLogPolicy;
use App\Modules\Access\Policies\RolePolicy;
use App\Modules\Access\Policies\UserPolicy;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Policies\EmployeePolicy;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Objects\Policies\CityPolicy;
use App\Modules\Objects\Policies\ObjectPolicy;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtRequests\Policies\NdtRequestPolicy;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\NdtTasks\Policies\NdtTaskPolicy;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Policies\OrganizationPolicy;
use App\Modules\Welds\Models\Weld;
use App\Modules\Welds\Policies\WeldPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            return $user->hasRole('Администратор системы') ? true : null;
        });

        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(City::class, CityPolicy::class);
        Gate::policy(NdtObject::class, ObjectPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Weld::class, WeldPolicy::class);
        Gate::policy(NdtRequest::class, NdtRequestPolicy::class);
        Gate::policy(NdtTask::class, NdtTaskPolicy::class);
    }
}
