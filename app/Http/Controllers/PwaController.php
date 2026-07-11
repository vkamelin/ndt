<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Enums\NdtTaskStatus;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PwaController extends Controller
{
    public function tasks(Request $request): View
    {
        $user = $request->user();
        $employeeId = $user?->primaryEmployee()?->getKey();

        $tasks = NdtTask::query()
            ->with(['object.city', 'method', 'request', 'assigneeEmployee.object.city', 'items.weld'])
            ->when($employeeId !== null, fn ($query) => $query->where('assignee_employee_id', $employeeId))
            ->orderByDesc('planned_date')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('pwa.tasks', [
            'tasks' => $tasks,
            'statuses' => NdtTaskStatus::options(),
        ]);
    }

    public function labShift(Request $request): View
    {
        $employee = $request->user()?->primaryEmployee();
        $objectId = $request->user()?->objectId();

        $currentShift = $employee === null ? null : Shift::query()
            ->with(['employee.object.city', 'labReport', 'labRegulatoryWorks', 'filmTransactions.film', 'chemicalTransactions.chemicalType', 'chemicalRequests.chemicalType'])
            ->where('employee_id', $employee->getKey())
            ->where('type', ShiftType::Lab)
            ->where('status', ShiftStatus::Open)
            ->first();

        return view('pwa.lab-shift', [
            'employee' => $employee,
            'currentShift' => $currentShift,
            'object' => $objectId === null ? null : NdtObject::query()->with('city')->find($objectId),
        ]);
    }

    public function decoder(Request $request): View
    {
        $employee = $request->user()?->primaryEmployee();
        $objectId = $request->user()?->objectId();

        $currentShift = $employee === null ? null : Shift::query()
            ->with(['employee.object.city', 'decoderReport', 'decoderFilmGroups', 'decoderRejects', 'decoderForgerySuspicion', 'decoderCleanups', 'decoderDecryptions'])
            ->where('employee_id', $employee->getKey())
            ->where('type', ShiftType::Decoder)
            ->where('status', ShiftStatus::Open)
            ->first();

        return view('pwa.decoder', [
            'employee' => $employee,
            'currentShift' => $currentShift,
            'object' => $objectId === null ? null : NdtObject::query()->with('city')->find($objectId),
        ]);
    }

    public function control(Request $request): View
    {
        $user = $request->user();
        $objectId = $user?->hasRole('Администратор системы') ? null : $user?->objectId();

        $tasks = NdtTask::query()
            ->with(['object.city', 'method', 'assigneeEmployee'])
            ->when($objectId !== null, fn ($query) => $query->where('object_id', $objectId))
            ->whereNotIn('status', [NdtTaskStatus::Completed->value, NdtTaskStatus::Cancelled->value])
            ->orderBy('planned_date')
            ->limit(8)
            ->get();

        $results = NdtResult::query()
            ->with(['weld.object.city', 'method', 'task'])
            ->when($objectId !== null, fn ($query) => $query->whereHas('weld', fn ($weldQuery) => $weldQuery->where('object_id', $objectId)))
            ->whereIn('status', [
                NdtResultStatus::InAnalysis->value,
                NdtResultStatus::ReadyForConclusion->value,
                NdtResultStatus::Defect->value,
            ])
            ->orderByDesc('control_date')
            ->limit(8)
            ->get();

        return view('pwa.control', [
            'tasks' => $tasks,
            'results' => $results,
            'object' => $objectId === null ? null : NdtObject::query()->with('city')->find($objectId),
        ]);
    }
}
