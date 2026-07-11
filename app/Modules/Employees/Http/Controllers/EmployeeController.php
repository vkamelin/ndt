<?php

declare(strict_types=1);

namespace App\Modules\Employees\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Employees\Http\Requests\StoreEmployeeQualificationRequest;
use App\Modules\Employees\Http\Requests\StoreEmployeeRequest;
use App\Modules\Employees\Http\Requests\UpdateEmployeeQualificationRequest;
use App\Modules\Employees\Http\Requests\UpdateEmployeeRequest;
use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\EmployeeQualification;
use App\Modules\Employees\Models\Position;
use App\Modules\Employees\Services\EmployeeService;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Employee::class);

        $query = Employee::query()
            ->with(['object.city', 'position', 'users'])
            ->when(! $request->user()->can('employees.manage') && $request->user() !== null, function ($query) use ($request): void {
                $query->where('object_id', $request->user()->objectId());
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('last_name', 'like', '%'.$search.'%')
                        ->orWhere('first_name', 'like', '%'.$search.'%')
                        ->orWhere('middle_name', 'like', '%'.$search.'%')
                        ->orWhere('personnel_number', 'like', '%'.$search.'%');
                });
            });

        return view('modules.employees.index', [
            'employees' => $query->orderBy('last_name')->paginate(15)->withQueryString(),
            'objects' => NdtObject::query()->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
            'users' => User::query()
                ->whereDoesntHave('employees')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('employees.manage');

        return view('modules.employees.create', [
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
            'users' => User::query()->whereDoesntHave('employees')->orderBy('name')->get(),
        ]);
    }

    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee);

        $employee->load(['object.city', 'position', 'users', 'qualifications']);

        $linkedUserIds = $employee->users()->pluck('users.id')->all();

        return view('modules.employees.show', [
            'employee' => $employee,
            'objects' => NdtObject::query()->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
            'users' => User::query()
                ->whereDoesntHave('employees')
                ->when($linkedUserIds !== [], function ($query) use ($linkedUserIds): void {
                    $query->orWhereKey($linkedUserIds);
                })
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Employee $employee): View
    {
        $this->authorize('employees.manage');

        $employee->load(['object.city', 'position', 'users', 'qualifications']);
        $linkedUserIds = $employee->users()->pluck('users.id')->all();

        return view('modules.employees.edit', [
            'employee' => $employee,
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'positions' => Position::query()->orderBy('name')->get(),
            'users' => User::query()
                ->whereDoesntHave('employees')
                ->when($linkedUserIds !== [], function ($query) use ($linkedUserIds): void {
                    $query->orWhereKey($linkedUserIds);
                })
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreEmployeeRequest $request, EmployeeService $employees): RedirectResponse
    {
        $this->authorize('employees.manage');

        $employee = $employees->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.employees.show', $employee)->with('status', 'Сотрудник создан.');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, EmployeeService $employees): RedirectResponse
    {
        $this->authorize('employees.manage');

        $updatedEmployee = $employees->update(
            employee: $employee,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.employees.show', $updatedEmployee)->with('status', 'Сотрудник обновлен.');
    }

    public function destroy(Request $request, Employee $employee, EmployeeService $employees): RedirectResponse
    {
        $this->authorize('employees.manage');

        $employees->deactivate(
            employee: $employee,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Сотрудник деактивирован.');
    }

    public function storeQualification(StoreEmployeeQualificationRequest $request, Employee $employee, EmployeeService $employees): RedirectResponse
    {
        $this->authorize('employees.manage');

        $employees->addQualification(
            employee: $employee,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Квалификация добавлена.');
    }

    public function updateQualification(UpdateEmployeeQualificationRequest $request, Employee $employee, EmployeeQualification $qualification, EmployeeService $employees): RedirectResponse
    {
        $this->authorize('employees.manage');

        $employees->updateQualification(
            qualification: $qualification,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Квалификация обновлена.');
    }

    public function destroyQualification(Request $request, Employee $employee, EmployeeQualification $qualification, EmployeeService $employees): RedirectResponse
    {
        $this->authorize('employees.manage');

        $employees->removeQualification(
            qualification: $qualification,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Квалификация удалена.');
    }
}
