<?php

declare(strict_types=1);

namespace App\Modules\NdtTasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Employees\Models\Employee;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtTasks\DTO\AssignNdtTaskData;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Http\Requests\StoreNdtTaskRequest;
use App\Modules\NdtTasks\Http\Requests\UpdateNdtTaskRequest;
use App\Modules\NdtTasks\Http\Requests\UpdateNdtTaskStatusRequest;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\NdtTasks\Services\NdtTaskService;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Models\Weld;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class NdtTaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', NdtTask::class);

        $query = NdtTask::query()
            ->with(['object.city', 'request', 'method', 'assigneeEmployee.users', 'welds'])
            ->when($request->filled('scope') && $request->string('scope')->toString() === 'overdue', function ($query): void {
                $query->whereDate('planned_date', '<', now()->toDateString())
                    ->whereNotIn('status', [NdtTaskStatus::Completed->value, NdtTaskStatus::Cancelled->value]);
            })
            ->when(
                $request->string('scope')->toString() === 'mine' || (! $request->user()->can('ndt_tasks.manage') && $request->user() !== null),
                function ($query) use ($request): void {
                    $employeeId = $request->user()?->primaryEmployee()?->getKey();
                    $query->where('assignee_employee_id', $employeeId);
                },
            )
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where('task_number', 'like', '%'.$search.'%');
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->where('object_id', (int) $request->input('object_id'));
            })
            ->when($request->filled('assignee_employee_id'), function ($query) use ($request): void {
                $query->where('assignee_employee_id', (int) $request->input('assignee_employee_id'));
            })
            ->when($request->filled('ndt_method_id'), function ($query) use ($request): void {
                $query->where('ndt_method_id', (int) $request->input('ndt_method_id'));
            });

        return view('modules.ndt-tasks.index', [
            'tasks' => $query->orderByDesc('planned_date')->orderByDesc('id')->paginate(15)->withQueryString(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'employees' => Employee::query()->with(['object.city', 'users'])->orderBy('last_name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'requests' => NdtRequest::query()->orderByDesc('id')->get(),
            'statuses' => NdtTaskStatus::options(),
            'welds' => Weld::query()
                ->with(['object.city', 'ndtMethods'])
                ->when($request->filled('object_id'), function ($query) use ($request): void {
                    $query->where('object_id', (int) $request->input('object_id'));
                })
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', NdtTask::class);

        $objectId = $request->user()?->objectId();

        return view('modules.ndt-tasks.create', [
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'employees' => Employee::query()->with(['object.city', 'users'])->orderBy('last_name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'requests' => NdtRequest::query()->orderByDesc('id')->get(),
            'welds' => Weld::query()
                ->with(['object.city', 'ndtMethods'])
                ->when($objectId !== null, function ($query) use ($objectId): void {
                    $query->where('object_id', $objectId);
                })
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function show(NdtTask $ndtTask): View
    {
        $this->authorize('view', $ndtTask);

        $ndtTask->load(['object.city', 'request', 'method', 'assigneeEmployee.object.city', 'assigneeEmployee.users', 'items.weld.object.city', 'items.weld.ndtMethods', 'statusHistory.changedBy']);

        return view('modules.ndt-tasks.show', [
            'task' => $ndtTask,
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'employees' => Employee::query()->with(['object.city', 'users'])->orderBy('last_name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'requests' => NdtRequest::query()->orderByDesc('id')->get(),
            'welds' => Weld::query()
                ->with(['object.city', 'ndtMethods'])
                ->where('object_id', $ndtTask->object_id)
                ->orderByDesc('id')
                ->get(),
            'statuses' => NdtTaskStatus::options(),
        ]);
    }

    public function edit(NdtTask $ndtTask): View
    {
        $this->authorize('update', $ndtTask);

        $ndtTask->load(['object.city', 'request', 'method', 'assigneeEmployee.object.city', 'assigneeEmployee.users', 'items.weld.object.city', 'items.weld.ndtMethods', 'statusHistory.changedBy']);

        return view('modules.ndt-tasks.edit', [
            'task' => $ndtTask,
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'employees' => Employee::query()->with(['object.city', 'users'])->orderBy('last_name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'requests' => NdtRequest::query()->orderByDesc('id')->get(),
            'welds' => Weld::query()
                ->with(['object.city', 'ndtMethods'])
                ->where('object_id', $ndtTask->object_id)
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(StoreNdtTaskRequest $request, NdtTaskService $tasks): RedirectResponse
    {
        $task = $tasks->create(
            data: AssignNdtTaskData::fromArray($request->validated()),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.ndt-tasks.show', $task)->with('status', 'Задание создано.');
    }

    public function update(UpdateNdtTaskRequest $request, NdtTask $ndtTask, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('update', $ndtTask);

        $task = $tasks->update(
            task: $ndtTask,
            data: AssignNdtTaskData::fromArray($request->validated()),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.ndt-tasks.show', $task)->with('status', 'Задание обновлено.');
    }

    public function accept(UpdateNdtTaskStatusRequest $request, NdtTask $ndtTask, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('accept', $ndtTask);

        $tasks->accept(
            task: $ndtTask,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Задание принято исполнителем.');
    }

    public function startWork(UpdateNdtTaskStatusRequest $request, NdtTask $ndtTask, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('startWork', $ndtTask);

        $tasks->startWork(
            task: $ndtTask,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Задание переведено в работу.');
    }

    public function complete(UpdateNdtTaskStatusRequest $request, NdtTask $ndtTask, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('complete', $ndtTask);

        $tasks->complete(
            task: $ndtTask,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Задание выполнено.');
    }

    public function completePartial(UpdateNdtTaskStatusRequest $request, NdtTask $ndtTask, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('completePartial', $ndtTask);

        $tasks->completePartial(
            task: $ndtTask,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Задание выполнено частично.');
    }

    public function returnTask(UpdateNdtTaskStatusRequest $request, NdtTask $ndtTask, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('returnTask', $ndtTask);

        $tasks->returnTask(
            task: $ndtTask,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Задание возвращено.');
    }

    public function cancel(UpdateNdtTaskStatusRequest $request, NdtTask $ndtTask, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('cancel', $ndtTask);

        $tasks->cancel(
            task: $ndtTask,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Задание отменено.');
    }
}
