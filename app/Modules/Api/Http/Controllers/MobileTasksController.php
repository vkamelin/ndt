<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Modules\Api\Http\Requests\StoreMobileTaskItemFileRequest;
use App\Modules\Api\Http\Requests\StoreMobileTaskItemResultRequest;
use App\Modules\Api\Http\Resources\FileResource;
use App\Modules\Api\Http\Resources\NdtResultResource;
use App\Modules\Api\Http\Resources\NdtTaskResource;
use App\Modules\Documents\Services\FileService;
use App\Modules\NdtResults\DTO\NdtResultData;
use App\Modules\NdtResults\Services\NdtResultService;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Http\Requests\UpdateNdtTaskStatusRequest;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\NdtTasks\Models\NdtTaskItem;
use App\Modules\NdtTasks\Services\NdtTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MobileTasksController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', NdtTask::class);

        $query = NdtTask::query()
            ->with(['object.city', 'request', 'method', 'assigneeEmployee.object.city', 'items.weld.object.city', 'items.weld.ndtMethods'])
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
                $query->where('task_number', 'like', '%'.$request->string('search')->toString().'%');
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->where('object_id', (int) $request->input('object_id'));
            })
            ->orderByDesc('planned_date')
            ->orderByDesc('id');

        return $this->paginated(
            $query->paginate((int) $request->input('per_page', 15))->withQueryString(),
            static fn (NdtTask $task): NdtTaskResource => new NdtTaskResource($task),
        );
    }

    public function show(NdtTask $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['object.city', 'request', 'method', 'assigneeEmployee.object.city', 'items.weld.object.city', 'items.weld.ndtMethods', 'items.files']);

        return $this->success(new NdtTaskResource($task));
    }

    public function accept(UpdateNdtTaskStatusRequest $request, NdtTask $task, NdtTaskService $tasks): JsonResponse
    {
        $this->authorize('accept', $task);

        $task = $tasks->accept(
            task: $task,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $task->load(['object.city', 'request', 'method', 'assigneeEmployee.object.city', 'items.weld.object.city', 'items.weld.ndtMethods', 'items.files']);

        return $this->success(new NdtTaskResource($task), 'Задание принято.');
    }

    public function completeItem(StoreMobileTaskItemResultRequest $request, NdtTask $task, NdtTaskItem $item, NdtTaskService $tasks, NdtResultService $results): JsonResponse
    {
        if ($item->ndt_task_id !== $task->getKey()) {
            abort(404);
        }

        if ($task->status === NdtTaskStatus::Accepted) {
            $this->authorize('startWork', $task);

            $task = $tasks->startWork(
                task: $task,
                actor: $request->user(),
                comment: $request->validated('comment') ?? null,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } else {
            $this->authorize('complete', $task);
        }

        $result = $results->create(
            data: NdtResultData::fromArray([
                'ndt_task_id' => $task->getKey(),
                'weld_id' => $item->weld_id,
                'ndt_method_id' => (int) $request->validated('ndt_method_id'),
                'executor_employee_id' => $request->validated('executor_employee_id') !== null ? (int) $request->validated('executor_employee_id') : null,
                'equipment_id' => $request->validated('equipment_id') !== null ? (int) $request->validated('equipment_id') : null,
                'normative_document_id' => $request->validated('normative_document_id') !== null ? (int) $request->validated('normative_document_id') : null,
                'control_date' => $request->validated('control_date'),
                'result_text' => $request->validated('result_text') ?? null,
                'comment' => $request->validated('comment') ?? null,
            ]),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $result->load(['weld.object.city', 'method']);

        return $this->created(new NdtResultResource($result), 'Результат сохранен.');
    }

    public function uploadItemFile(StoreMobileTaskItemFileRequest $request, NdtTask $task, NdtTaskItem $item, FileService $files): JsonResponse
    {
        $this->authorize('manage', $item);

        if ($item->ndt_task_id !== $task->getKey()) {
            abort(404);
        }

        $file = $files->store(
            upload: $request->file('file'),
            actor: $request->user(),
            related: $item,
        );

        return $this->created(new FileResource($file), 'Файл загружен.');
    }

    public function finish(UpdateNdtTaskStatusRequest $request, NdtTask $task, NdtTaskService $tasks): JsonResponse
    {
        if ($task->status === NdtTaskStatus::Accepted) {
            $this->authorize('startWork', $task);

            $task = $tasks->startWork(
                task: $task,
                actor: $request->user(),
                comment: $request->validated('comment') ?? null,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } else {
            $this->authorize('complete', $task);
        }

        $task = $tasks->complete(
            task: $task,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $task->load(['object.city', 'request', 'method', 'assigneeEmployee.object.city', 'items.weld.object.city', 'items.weld.ndtMethods', 'items.files']);

        return $this->success(new NdtTaskResource($task), 'Задание завершено.');
    }
}
