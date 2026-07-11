<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\DefectType;
use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Employees\Models\Employee;
use App\Modules\Equipment\Enums\EquipmentStatus;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\NdtResults\DTO\MagneticControlData;
use App\Modules\NdtResults\DTO\NdtResultData;
use App\Modules\NdtResults\DTO\PenetrantControlData;
use App\Modules\NdtResults\DTO\UltrasonicControlData;
use App\Modules\NdtResults\DTO\VisualControlData;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Http\Requests\StoreMagneticControlRequest;
use App\Modules\NdtResults\Http\Requests\StoreNdtResultDefectRequest;
use App\Modules\NdtResults\Http\Requests\StoreNdtResultRequest;
use App\Modules\NdtResults\Http\Requests\StorePenetrantControlRequest;
use App\Modules\NdtResults\Http\Requests\StoreUltrasonicControlRequest;
use App\Modules\NdtResults\Http\Requests\StoreVisualControlRequest;
use App\Modules\NdtResults\Http\Requests\UpdateNdtResultRequest;
use App\Modules\NdtResults\Http\Requests\UpdateNdtResultStatusRequest;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtResults\Services\MagneticControlService;
use App\Modules\NdtResults\Services\NdtResultService;
use App\Modules\NdtResults\Services\PenetrantControlService;
use App\Modules\NdtResults\Services\UltrasonicControlService;
use App\Modules\NdtResults\Services\VisualControlService;
use App\Modules\NdtTasks\Enums\NdtMethodCode;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Models\Weld;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class NdtResultController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', NdtResult::class);
        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        $results = NdtResult::query()
            ->with(['weld.object.city', 'method', 'executorEmployee.object.city', 'defects.defectType'])
            ->when(! $isAdmin, function ($query) use ($objectId): void {
                $query->whereHas('weld', function ($subQuery) use ($objectId): void {
                    if ($objectId === null) {
                        $subQuery->whereRaw('1 = 0');

                        return;
                    }

                    $subQuery->where('object_id', $objectId);
                });
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->whereHas('weld', function ($subQuery) use ($search): void {
                    $subQuery->where('weld_number', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('ndt_method_id'), function ($query) use ($request): void {
                $query->where('ndt_method_id', (int) $request->input('ndt_method_id'));
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->whereHas('weld', function ($subQuery) use ($request): void {
                    $subQuery->where('object_id', (int) $request->input('object_id'));
                });
            });

        return view('modules.ndt-results.index', [
            'results' => $results->orderByDesc('control_date')->orderByDesc('id')->paginate(15)->withQueryString(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => NdtResultStatus::options(),
            'tasks' => NdtTask::query()
                ->with(['object.city', 'method', 'assigneeEmployee'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderByDesc('id')
                ->get(),
            'welds' => Weld::query()
                ->with(['object.city', 'ndtMethods'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderByDesc('id')
                ->get(),
            'objects' => NdtObject::query()
                ->with('city')
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereKey($objectId);
                })
                ->orderBy('name')
                ->get(),
            'employees' => Employee::query()
                ->with(['object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderBy('last_name')
                ->get(),
            'equipment' => Equipment::query()
                ->with(['type', 'object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->whereIn('status', [EquipmentStatus::Available->value, EquipmentStatus::Issued->value])
                ->orderBy('name')
                ->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', NdtResult::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        return view('modules.ndt-results.create', [
            'tasks' => NdtTask::query()
                ->with(['object.city', 'method', 'assigneeEmployee'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderByDesc('id')
                ->get(),
            'welds' => Weld::query()
                ->with(['object.city', 'ndtMethods'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderByDesc('id')
                ->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'objects' => NdtObject::query()
                ->with('city')
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereKey($objectId);
                })
                ->orderBy('name')
                ->get(),
            'employees' => Employee::query()
                ->with(['object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderBy('last_name')
                ->get(),
            'equipment' => Equipment::query()
                ->with(['type', 'object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->whereIn('status', [EquipmentStatus::Available->value, EquipmentStatus::Issued->value])
                ->orderBy('name')
                ->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
            'statuses' => NdtResultStatus::options(),
        ]);
    }

    public function show(Request $request, NdtResult $ndtResult): View
    {
        $this->authorize('view', $ndtResult);
        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        $ndtResult->load([
            'task.object.city',
            'weld.object.city',
            'method',
            'executorEmployee.object.city',
            'defects.defectType',
            'statusHistory.changedBy',
            'vtResult',
            'ptResult',
            'mtResult',
            'utResult',
        ]);

        return view('modules.ndt-results.show', [
            'result' => $ndtResult,
            'defectTypes' => DefectType::query()->where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::query()
                ->with(['object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderBy('last_name')
                ->get(),
            'equipment' => Equipment::query()
                ->with(['type', 'object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->whereIn('status', [EquipmentStatus::Available->value, EquipmentStatus::Issued->value])
                ->orderBy('name')
                ->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
        ]);
    }

    public function edit(Request $request, NdtResult $ndtResult): View
    {
        $this->authorize('update', $ndtResult);
        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        $ndtResult->load([
            'task.object.city',
            'weld.object.city',
            'method',
            'executorEmployee.object.city',
            'defects.defectType',
            'statusHistory.changedBy',
            'vtResult',
            'ptResult',
            'mtResult',
            'utResult',
        ]);

        return view('modules.ndt-results.edit', [
            'result' => $ndtResult,
            'defectTypes' => DefectType::query()->where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::query()
                ->with(['object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->orderBy('last_name')
                ->get(),
            'equipment' => Equipment::query()
                ->with(['type', 'object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->where('object_id', $objectId);
                })
                ->whereIn('status', [EquipmentStatus::Available->value, EquipmentStatus::Issued->value])
                ->orderBy('name')
                ->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreNdtResultRequest $request, NdtResultService $results): RedirectResponse
    {
        $ndtTask = NdtTask::query()->with('assigneeEmployee')->findOrFail((int) $request->validated('ndt_task_id'));
        $this->authorize('create', [NdtResult::class, $ndtTask]);

        $result = $results->create(
            data: NdtResultData::fromArray([
                'ndt_task_id' => $ndtTask->getKey(),
                'weld_id' => (int) $request->validated('weld_id'),
                'ndt_method_id' => $ndtTask->ndt_method_id,
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

        return redirect()->route('admin.ndt-results.show', $result)->with('status', 'Результат создан.');
    }

    public function update(UpdateNdtResultRequest $request, NdtResult $ndtResult, NdtResultService $results): RedirectResponse
    {
        $this->authorize('update', $ndtResult);

        $result = $results->update(
            result: $ndtResult,
            data: NdtResultData::fromArray([
                'ndt_task_id' => $ndtResult->ndt_task_id,
                'weld_id' => $ndtResult->weld_id,
                'ndt_method_id' => $ndtResult->ndt_method_id,
                'executor_employee_id' => (int) $request->validated('executor_employee_id'),
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

        return redirect()->route('admin.ndt-results.show', $result)->with('status', 'Результат обновлен.');
    }

    public function sendToAnalysis(UpdateNdtResultStatusRequest $request, NdtResult $ndtResult, NdtResultService $results): RedirectResponse
    {
        $this->authorize('manage', $ndtResult);

        $results->sendToAnalysis(
            result: $ndtResult,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Результат передан на анализ.');
    }

    public function markDefect(UpdateNdtResultStatusRequest $request, NdtResult $ndtResult, NdtResultService $results): RedirectResponse
    {
        $this->authorize('analyze', $ndtResult);

        $results->markDefect(
            result: $ndtResult,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Результат отмечен как дефектный.');
    }

    public function markReadyForConclusion(UpdateNdtResultStatusRequest $request, NdtResult $ndtResult, NdtResultService $results): RedirectResponse
    {
        $this->authorize('analyze', $ndtResult);

        $results->markReadyForConclusion(
            result: $ndtResult,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Результат готов к заключению.');
    }

    public function returnForCorrection(UpdateNdtResultStatusRequest $request, NdtResult $ndtResult, NdtResultService $results): RedirectResponse
    {
        $this->authorize('approve', $ndtResult);

        $results->returnForCorrection(
            result: $ndtResult,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Результат возвращен на доработку.');
    }

    public function approve(UpdateNdtResultStatusRequest $request, NdtResult $ndtResult, NdtResultService $results): RedirectResponse
    {
        $this->authorize('approve', $ndtResult);

        $results->approve(
            result: $ndtResult,
            actor: $request->user(),
            comment: $request->validated('comment') ?? null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Результат утвержден.');
    }

    public function storeDefect(StoreNdtResultDefectRequest $request, NdtResult $ndtResult, NdtResultService $results): RedirectResponse
    {
        $this->authorize('analyze', $ndtResult);

        $results->addDefect(
            result: $ndtResult,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Дефект добавлен.');
    }

    public function updateVisualControl(StoreVisualControlRequest $request, NdtResult $ndtResult, VisualControlService $service): RedirectResponse
    {
        $this->authorize('analyze', $ndtResult);
        $this->ensureMethod($ndtResult, NdtMethodCode::VIK);

        $service->save($ndtResult, VisualControlData::fromArray($request->validated()), $request->user());

        return back()->with('status', 'Форма ВИК сохранена.');
    }

    public function updatePenetrantControl(StorePenetrantControlRequest $request, NdtResult $ndtResult, PenetrantControlService $service): RedirectResponse
    {
        $this->authorize('analyze', $ndtResult);
        $this->ensureMethod($ndtResult, NdtMethodCode::PVK);

        $service->save($ndtResult, PenetrantControlData::fromArray($request->validated()), $request->user());

        return back()->with('status', 'Форма ПВК сохранена.');
    }

    public function updateMagneticControl(StoreMagneticControlRequest $request, NdtResult $ndtResult, MagneticControlService $service): RedirectResponse
    {
        $this->authorize('analyze', $ndtResult);
        $this->ensureMethod($ndtResult, NdtMethodCode::MK);

        $service->save($ndtResult, MagneticControlData::fromArray($request->validated()), $request->user());

        return back()->with('status', 'Форма МК сохранена.');
    }

    public function updateUltrasonicControl(StoreUltrasonicControlRequest $request, NdtResult $ndtResult, UltrasonicControlService $service): RedirectResponse
    {
        $this->authorize('analyze', $ndtResult);
        $this->ensureMethod($ndtResult, NdtMethodCode::UK);

        $service->save($ndtResult, UltrasonicControlData::fromArray($request->validated()), $request->user());

        return back()->with('status', 'Форма УК сохранена.');
    }

    private function ensureMethod(NdtResult $result, NdtMethodCode $methodCode): void
    {
        abort_unless($result->method?->code === $methodCode, 404);
    }
}
