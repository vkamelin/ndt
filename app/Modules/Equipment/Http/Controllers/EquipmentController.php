<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Employees\Models\Employee;
use App\Modules\Equipment\Enums\EquipmentStatus;
use App\Modules\Equipment\Http\Requests\ReturnEquipmentAssignmentRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentAssignmentRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentCalibrationRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentDefectRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentDocumentRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentMovementRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentRepairRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentRequest;
use App\Modules\Equipment\Http\Requests\StoreEquipmentVerificationRequest;
use App\Modules\Equipment\Http\Requests\UpdateEquipmentRequest;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Equipment\Models\EquipmentAssignment;
use App\Modules\Equipment\Models\EquipmentType;
use App\Modules\Equipment\Services\EquipmentService;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Equipment::class);
        $user = $request->user();
        $objectId = $user?->objectId();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;

        $equipment = Equipment::query()
            ->with(['type', 'object.city', 'latestVerification', 'latestCalibration'])
            ->when(! $isAdmin, function ($query) use ($objectId): void {
                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('inventory_number', 'like', '%'.$search.'%')
                        ->orWhere('serial_number', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('equipment_type_id'), function ($query) use ($request): void {
                $query->where('equipment_type_id', (int) $request->input('equipment_type_id'));
            });

        return view('modules.equipment.index', [
            'equipment' => $equipment->orderBy('name')->paginate(15)->withQueryString(),
            'equipmentTypes' => EquipmentType::query()->orderBy('name')->get(),
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
            'statuses' => EquipmentStatus::options(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Equipment::class);

        $user = $request->user();
        $objectId = $user?->objectId();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;

        return view('modules.equipment.create', [
            'equipmentTypes' => EquipmentType::query()->where('is_active', true)->orderBy('name')->get(),
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
            'statuses' => EquipmentStatus::options(),
        ]);
    }

    public function show(Request $request, Equipment $equipment): View
    {
        $this->authorize('view', $equipment);

        $equipment->load([
            'type',
            'object.city',
            'verifications.recordedBy',
            'calibrations.recordedBy',
            'repairs.recordedBy',
            'assignments.employee.object.city',
            'movements.fromObject.city',
            'movements.toObject.city',
            'defects.recordedBy',
            'documents.recordedBy',
            'currentAssignment.employee.object.city',
        ]);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        return view('modules.equipment.show', [
            'equipment' => $equipment,
            'equipmentTypes' => EquipmentType::query()->where('is_active', true)->orderBy('name')->get(),
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
            'statuses' => EquipmentStatus::options(),
        ]);
    }

    public function edit(Request $request, Equipment $equipment): View
    {
        $this->authorize('manage', $equipment);

        $equipment->load([
            'type',
            'object.city',
            'verifications.recordedBy',
            'calibrations.recordedBy',
            'repairs.recordedBy',
            'assignments.employee.object.city',
            'movements.fromObject.city',
            'movements.toObject.city',
            'defects.recordedBy',
            'documents.recordedBy',
            'currentAssignment.employee.object.city',
        ]);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        return view('modules.equipment.edit', [
            'equipment' => $equipment,
            'equipmentTypes' => EquipmentType::query()->where('is_active', true)->orderBy('name')->get(),
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
            'statuses' => EquipmentStatus::options(),
        ]);
    }

    public function store(StoreEquipmentRequest $request, EquipmentService $equipment): RedirectResponse
    {
        $this->authorize('equipment.manage');

        $item = $equipment->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.equipment.show', $item)->with('status', 'Оборудование создано.');
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $item = $equipmentService->update(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.equipment.show', $item)->with('status', 'Оборудование обновлено.');
    }

    public function destroy(Request $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->writeOff(
            equipment: $equipment,
            actor: $request->user(),
            comment: $request->input('comment'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Оборудование списано.');
    }

    public function storeVerification(StoreEquipmentVerificationRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->recordVerification(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Поверка сохранена.');
    }

    public function storeCalibration(StoreEquipmentCalibrationRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->recordCalibration(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Калибровка сохранена.');
    }

    public function storeRepair(StoreEquipmentRepairRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->recordRepair(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Ремонт сохранен.');
    }

    public function storeAssignment(StoreEquipmentAssignmentRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->issue(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Выдача сохранена.');
    }

    public function returnAssignment(ReturnEquipmentAssignmentRequest $request, Equipment $equipment, EquipmentAssignment $assignment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->returnAssignment(
            assignment: $assignment,
            actor: $request->user(),
            returnedAt: $request->validated('returned_at'),
            comment: $request->validated('comment'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Возврат сохранен.');
    }

    public function storeMovement(StoreEquipmentMovementRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->recordMovement(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Перемещение сохранено.');
    }

    public function storeDefect(StoreEquipmentDefectRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->recordDefect(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Дефект сохранен.');
    }

    public function storeDocument(StoreEquipmentDocumentRequest $request, Equipment $equipment, EquipmentService $equipmentService): RedirectResponse
    {
        $this->authorize('manage', $equipment);

        $equipmentService->addDocument(
            equipment: $equipment,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Документ сохранен.');
    }
}
