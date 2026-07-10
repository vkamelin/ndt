<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Modules\Api\Http\Resources\ShiftResource;
use App\Modules\Employees\Models\Employee;
use App\Modules\Inventory\Models\ChemicalInventoryTransaction;
use App\Modules\Inventory\Models\ChemicalRequest;
use App\Modules\Inventory\Models\FilmInventoryTransaction;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MobileShiftsController extends ApiController
{
    public function current(Request $request): JsonResponse
    {
        $employee = $request->user()?->primaryEmployee();

        if ($employee === null) {
            return $this->success(['shift' => null]);
        }

        $query = Shift::query()
            ->with(['employee.object.city', 'labReport', 'decoderReport'])
            ->where('employee_id', $employee->getKey())
            ->where('status', \App\Modules\Shifts\Enums\ShiftStatus::Open);

        if ($request->filled('type') && in_array($request->string('type')->toString(), ['lab', 'decoder'], true)) {
            $query->where('type', ShiftType::from($request->string('type')->toString()));
        }

        $shift = $query->orderByDesc('started_at')->first();

        return $this->success([
            'shift' => $shift === null ? null : new ShiftResource($shift),
        ]);
    }

    public function start(StoreShiftRequest $request, LabShiftService $labShifts, DecoderShiftService $decoderShifts): JsonResponse
    {
        $employee = Employee::query()->with('object')->findOrFail((int) $request->validated('employee_id'));
        $this->authorize('create', [Shift::class, $employee]);

        $shift = match (ShiftType::from($request->validated('type'))) {
            ShiftType::Lab => $labShifts->start($employee, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
            ShiftType::Decoder => $decoderShifts->start($employee, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
        };

        $shift->load(['employee.object.city', 'labReport', 'decoderReport']);

        return $this->created(new ShiftResource($shift), 'Смена открыта.');
    }

    public function finish(CompleteShiftRequest $request, Shift $shift, LabShiftService $labShifts, DecoderShiftService $decoderShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $shift = match ($shift->type) {
            ShiftType::Lab => $labShifts->complete($shift, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
            ShiftType::Decoder => $decoderShifts->complete($shift, $request->validated('comment') ?? null, $request->user(), $request->ip(), $request->userAgent()),
        };

        $shift->load(['employee.object.city', 'labReport', 'decoderReport']);

        return $this->success(new ShiftResource($shift), 'Смена завершена.');
    }

    public function storeLabReport(StoreLabShiftReportRequest $request, Shift $shift, LabShiftService $labShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->addReport($shift, [
            'summary' => $request->validated('summary') ?? null,
            'comment' => $request->validated('comment') ?? null,
            'completed_at' => $request->validated('completed_at') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Отчет сохранен.');
    }

    public function storeLabRegulatoryWork(StoreLabShiftRegulatoryWorkRequest $request, Shift $shift, LabShiftService $labShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->addRegulatoryWork($shift, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Регламентные работы сохранены.');
    }

    public function storeFilmTransaction(StoreFilmInventoryTransactionRequest $request, Shift $shift, LabShiftService $labShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->receiveFilm($shift, [
            'rt_film_id' => $request->validated('rt_film_id') !== null ? (int) $request->validated('rt_film_id') : null,
            'quantity' => $request->validated('quantity') !== null ? (int) $request->validated('quantity') : null,
            'transacted_at' => $request->validated('transacted_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Движение пленки сохранено.');
    }

    public function storeChemicalTransaction(StoreChemicalInventoryTransactionRequest $request, Shift $shift, LabShiftService $labShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->receiveChemical($shift, [
            'chemical_type_id' => $request->validated('chemical_type_id') !== null ? (int) $request->validated('chemical_type_id') : null,
            'quantity' => $request->validated('quantity') !== null ? (int) $request->validated('quantity') : null,
            'transacted_at' => $request->validated('transacted_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Движение химии сохранено.');
    }

    public function storeChemicalRequest(StoreChemicalRequestRequest $request, Shift $shift, LabShiftService $labShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $labShifts->requestChemical($shift, [
            'chemical_type_id' => $request->validated('chemical_type_id') !== null ? (int) $request->validated('chemical_type_id') : null,
            'quantity' => $request->validated('quantity') !== null ? (int) $request->validated('quantity') : null,
            'requested_at' => $request->validated('requested_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Запрос химии сохранен.');
    }

    public function storeDecoderReport(StoreDecoderShiftReportRequest $request, Shift $shift, DecoderShiftService $decoderShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addReport($shift, [
            'summary' => $request->validated('summary') ?? null,
            'comment' => $request->validated('comment') ?? null,
            'completed_at' => $request->validated('completed_at') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Отчет сохранен.');
    }

    public function storeDecoderFilmGroup(StoreDecoderFilmGroupRequest $request, Shift $shift, DecoderShiftService $decoderShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addFilmGroup($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'group_name' => $request->validated('group_name'),
            'viewed_at' => $request->validated('viewed_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Группа просмотрена.');
    }

    public function storeDecoderReject(StoreDecoderRejectRequest $request, Shift $shift, DecoderShiftService $decoderShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addReject($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'reason' => $request->validated('reason'),
            'recorded_at' => $request->validated('recorded_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Брак зафиксирован.');
    }

    public function storeDecoderForgerySuspicion(StoreDecoderForgerySuspicionRequest $request, Shift $shift, DecoderShiftService $decoderShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addForgerySuspicion($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'reason' => $request->validated('reason'),
            'recorded_at' => $request->validated('recorded_at') ?? null,
            'comment' => $request->validated('comment') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Подозрение на подлог зафиксировано.');
    }

    public function storeDecoderCleanup(StoreDecoderCleanupRequest $request, Shift $shift, DecoderShiftService $decoderShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addCleanup($shift, $request->validated(), $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Очистка рабочего места сохранена.');
    }

    public function storeDecoderDecryption(StoreDecoderDecryptionRequest $request, Shift $shift, DecoderShiftService $decoderShifts): JsonResponse
    {
        $this->authorize('manage', $shift);

        $decoderShifts->addDecryption($shift, [
            'rt_result_id' => $request->validated('rt_result_id') !== null ? (int) $request->validated('rt_result_id') : null,
            'result_text' => $request->validated('result_text') ?? null,
            'analysis_comment' => $request->validated('analysis_comment') ?? null,
            'decrypted_at' => $request->validated('decrypted_at') ?? null,
        ], $request->user(), $request->ip(), $request->userAgent());

        return $this->success($this->loadShift($shift), 'Дешифровка сохранена.');
    }

    private function loadShift(Shift $shift): ShiftResource
    {
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

        return new ShiftResource($shift);
    }
}
