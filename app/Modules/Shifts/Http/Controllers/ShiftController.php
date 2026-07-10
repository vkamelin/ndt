<?php

declare(strict_types=1);

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\ChemicalType;
use App\Modules\Admin\Models\FilmType;
use App\Modules\Employees\Models\Employee;
use App\Modules\Inventory\Models\ChemicalInventoryTransaction;
use App\Modules\Inventory\Models\ChemicalRequest;
use App\Modules\Inventory\Models\FilmInventoryTransaction;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\Shifts\Enums\ShiftStatus;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Http\Requests\CompleteShiftRequest;
use App\Modules\Shifts\Http\Requests\StoreChemicalInventoryTransactionRequest;
use App\Modules\Shifts\Http\Requests\StoreChemicalRequestRequest;
use App\Modules\Shifts\Http\Requests\StoreDecoderCleanupRequest;
use App\Modules\Shifts\Http\Requests\StoreDecoderDecryptionRequest;
use App\Modules\Shifts\Http\Requests\StoreDecoderFilmGroupRequest;
use App\Modules\Shifts\Http\Requests\StoreDecoderForgerySuspicionRequest;
use App\Modules\Shifts\Http\Requests\StoreDecoderRejectRequest;
use App\Modules\Shifts\Http\Requests\StoreDecoderShiftReportRequest;
use App\Modules\Shifts\Http\Requests\StoreFilmInventoryTransactionRequest;
use App\Modules\Shifts\Http\Requests\StoreLabShiftRegulatoryWorkRequest;
use App\Modules\Shifts\Http\Requests\StoreLabShiftReportRequest;
use App\Modules\Shifts\Http\Requests\StoreShiftRequest;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Services\DecoderShiftService;
use App\Modules\Shifts\Services\LabShiftService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ShiftController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Shift::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        $shifts = Shift::query()
            ->with(['employee.object.city', 'labReport', 'decoderReport'])
            ->when(! $isAdmin, function ($query) use ($objectId): void {
                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->orderByDesc('started_at')
            ->paginate(15)
            ->withQueryString();

        return view('modules.shifts.index', [
            'shifts' => $shifts,
            'types' => ShiftType::options(),
            'statuses' => ShiftStatus::options(),
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
        ]);
    }

    public function show(Request $request, Shift $shift): View
    {
        $this->authorize('view', $shift);

        $shift->load([
            'employee.object.city',
            'labReport',
            'labRegulatoryWorks',
            'filmTransactions.film',
            'chemicalTransactions.chemicalType',
            'chemicalRequests.chemicalType',
            'decoderReport',
            'decoderFilmGroups.result.ndtResult.weld',
            'decoderRejects.result.ndtResult.weld',
            'decoderForgerySuspicion.result.ndtResult.weld',
            'decoderCleanups',
            'decoderDecryptions.result.ndtResult.weld',
        ]);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        return view('modules.shifts.show', [
            'shift' => $shift,
            'types' => ShiftType::options(),
            'statuses' => ShiftStatus::options(),
            'filmTypes' => FilmType::query()->where('is_active', true)->orderBy('name')->get(),
            'chemicalTypes' => ChemicalType::query()->where('is_active', true)->orderBy('name')->get(),
            'rtResults' => NdtResult::query()
                ->with(['weld.object.city'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas('weld', function ($weldQuery) use ($objectId): void {
                        $weldQuery->where('object_id', $objectId);
                    });
                })
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(StoreShiftRequest $request, LabShiftService $labShifts, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $employee = Employee::query()->with('object')->findOrFail((int) $request->validated('employee_id'));
        $this->authorize('create', [Shift::class, $employee]);

        $type = ShiftType::from($request->validated('type'));

        match ($type) {
            ShiftType::Lab => $labShifts->start($employee, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
            ShiftType::Decoder => $decoderShifts->start($employee, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
        };

        return back()->with('status', 'Смена открыта.');
    }

    public function complete(CompleteShiftRequest $request, Shift $shift, LabShiftService $labShifts, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        match ($shift->type) {
            ShiftType::Lab => $labShifts->complete($shift, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
            ShiftType::Decoder => $decoderShifts->complete($shift, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
        };

        return back()->with('status', 'Смена завершена.');
    }

    public function storeLabReport(StoreLabShiftReportRequest $request, Shift $shift, LabShiftService $labShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->addReport($shift, [
            'summary' => $request->validated('summary') ?? null,
            'comment' => $request->validated('comment') ?? null,
            'completed_at' => $request->validated('completed_at') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Отчет смены лаборанта сохранен.');
    }

    public function storeLabRegulatoryWork(StoreLabShiftRegulatoryWorkRequest $request, Shift $shift, LabShiftService $labShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->addRegulatoryWork($shift, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Регламентные работы сохранены.');
    }

    public function storeFilmTransaction(StoreFilmInventoryTransactionRequest $request, Shift $shift, LabShiftService $labShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->receiveFilm($shift, [
            'rt_film_id' => $request->validated('rt_film_id') !== null ? (int) $request->validated('rt_film_id') : null,
            'quantity' => $request->validated('quantity') !== null ? (int) $request->validated('quantity') : null,
            'transacted_at' => $request->validated('transacted_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Движение пленки сохранено.');
    }

    public function storeChemicalTransaction(StoreChemicalInventoryTransactionRequest $request, Shift $shift, LabShiftService $labShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->receiveChemical($shift, [
            'chemical_type_id' => $request->validated('chemical_type_id') !== null ? (int) $request->validated('chemical_type_id') : null,
            'quantity' => $request->validated('quantity') !== null ? (int) $request->validated('quantity') : null,
            'transacted_at' => $request->validated('transacted_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Движение химии сохранено.');
    }

    public function storeChemicalRequest(StoreChemicalRequestRequest $request, Shift $shift, LabShiftService $labShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->requestChemical($shift, [
            'chemical_type_id' => $request->validated('chemical_type_id') !== null ? (int) $request->validated('chemical_type_id') : null,
            'quantity' => $request->validated('quantity') !== null ? (int) $request->validated('quantity') : null,
            'requested_at' => $request->validated('requested_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Запрос химии сохранен.');
    }

    public function storeDecoderReport(StoreDecoderShiftReportRequest $request, Shift $shift, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addReport($shift, [
            'summary' => $request->validated('summary') ?? null,
            'comment' => $request->validated('comment') ?? null,
            'completed_at' => $request->validated('completed_at') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Отчет смены дешифровщика сохранен.');
    }

    public function storeDecoderFilmGroup(StoreDecoderFilmGroupRequest $request, Shift $shift, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addFilmGroup($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'group_name' => $request->validated('group_name'),
            'viewed_at' => $request->validated('viewed_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Группа пленок/снимков сохранена.');
    }

    public function storeDecoderReject(StoreDecoderRejectRequest $request, Shift $shift, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addReject($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'reason' => $request->validated('reason'),
            'recorded_at' => $request->validated('recorded_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Брак зафиксирован.');
    }

    public function storeDecoderForgerySuspicion(StoreDecoderForgerySuspicionRequest $request, Shift $shift, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addForgerySuspicion($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'reason' => $request->validated('reason'),
            'recorded_at' => $request->validated('recorded_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Подозрение на подлог зафиксировано.');
    }

    public function storeDecoderCleanup(StoreDecoderCleanupRequest $request, Shift $shift, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addCleanup($shift, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Очистка рабочего места сохранена.');
    }

    public function storeDecoderDecryption(StoreDecoderDecryptionRequest $request, Shift $shift, DecoderShiftService $decoderShifts): RedirectResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addDecryption($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'result_text' => $request->validated('result_text') ?? null,
            'analysis_comment' => $request->validated('analysis_comment') ?? null,
            'decrypted_at' => $request->validated('decrypted_at') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return back()->with('status', 'Дешифровка сохранена.');
    }
}
