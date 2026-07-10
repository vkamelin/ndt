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
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\File;
use App\Modules\Documents\Policies\DocumentPolicy;
use App\Modules\Documents\Policies\FilePolicy;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Conclusions\Policies\ConclusionPolicy;
use App\Modules\Registers\Models\Act;
use App\Modules\Registers\Models\ArchiveCase;
use App\Modules\Registers\Models\TransferRegister;
use App\Modules\Registers\Policies\ActPolicy;
use App\Modules\Registers\Policies\ArchiveCasePolicy;
use App\Modules\Registers\Policies\TransferRegisterPolicy;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Objects\Policies\CityPolicy;
use App\Modules\Objects\Policies\ObjectPolicy;
use App\Modules\Equipment\Policies\EquipmentPolicy;
use App\Modules\Equipment\Services\EquipmentAvailabilityService;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtRequests\Policies\NdtRequestPolicy;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtResults\Policies\NdtResultPolicy;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Radiography\Policies\RtResultPolicy;
use App\Modules\NdtResults\Services\EquipmentAvailabilityServiceInterface;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\NdtTasks\Models\NdtTaskItem;
use App\Modules\NdtTasks\Policies\NdtTaskPolicy;
use App\Modules\NdtTasks\Policies\NdtTaskItemPolicy;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Policies\OrganizationPolicy;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Policies\ShiftPolicy;
use App\Modules\Welds\Models\Weld;
use App\Modules\Welds\Policies\WeldPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Permission\Models\Role;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EquipmentAvailabilityServiceInterface::class, EquipmentAvailabilityService::class);
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
        Gate::policy(\App\Modules\Equipment\Models\Equipment::class, EquipmentPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Weld::class, WeldPolicy::class);
        Gate::policy(NdtRequest::class, NdtRequestPolicy::class);
        Gate::policy(NdtResult::class, NdtResultPolicy::class);
        Gate::policy(RtResult::class, RtResultPolicy::class);
        Gate::policy(NdtTask::class, NdtTaskPolicy::class);
        Gate::policy(NdtTaskItem::class, NdtTaskItemPolicy::class);
        Gate::policy(Shift::class, ShiftPolicy::class);
        Gate::policy(Conclusion::class, ConclusionPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(File::class, FilePolicy::class);
        Gate::policy(TransferRegister::class, TransferRegisterPolicy::class);
        Gate::policy(Act::class, ActPolicy::class);
        Gate::policy(ArchiveCase::class, ArchiveCasePolicy::class);

        RateLimiter::for('api', function (Request $request): Limit {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinute(120)->by((string) $key);
        });

        RateLimiter::for('api-auth', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
