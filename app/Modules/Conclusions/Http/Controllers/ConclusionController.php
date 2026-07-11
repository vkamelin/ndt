<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Conclusions\Enums\ConclusionStatus;
use App\Modules\Conclusions\Http\Requests\AnnulConclusionRequest;
use App\Modules\Conclusions\Http\Requests\ApproveConclusionRequest;
use App\Modules\Conclusions\Http\Requests\IssueConclusionRequest;
use App\Modules\Conclusions\Http\Requests\ReplaceConclusionRequest;
use App\Modules\Conclusions\Http\Requests\StoreConclusionRequest;
use App\Modules\Conclusions\Http\Requests\StoreConclusionVersionRequest;
use App\Modules\Conclusions\Http\Requests\SubmitConclusionRequest;
use App\Modules\Conclusions\Http\Requests\UpdateConclusionRequest;
use App\Modules\Conclusions\Jobs\GenerateConclusionPdfJob;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Conclusions\Services\ConclusionService;
use App\Modules\Documents\Http\Requests\StoreFileRequest;
use App\Modules\Documents\Services\FileService;
use App\Modules\Employees\Models\Employee;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\Objects\Models\NdtObject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ConclusionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Conclusion::class);

        $user = $request->user();
        $objectId = $user?->objectId();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;

        $conclusions = Conclusion::query()
            ->with(['object.city', 'method', 'request', 'preparedBy', 'checkedBy', 'approvedBy', 'latestVersion.file'])
            ->withCount(['items', 'versions', 'files'])
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
                    $nested->where('number', 'like', '%'.$search.'%')
                        ->orWhereHas('request', function ($subQuery) use ($search): void {
                            $subQuery->where('request_number', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->where('object_id', (int) $request->input('object_id'));
            })
            ->when($request->filled('ndt_method_id'), function ($query) use ($request): void {
                $query->where('ndt_method_id', (int) $request->input('ndt_method_id'));
            });

        return view('modules.conclusions.index', [
            'conclusions' => $conclusions->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString(),
            'statuses' => ConclusionStatus::options(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'requests' => NdtRequest::query()->with('object.city')->orderByDesc('request_date')->get(),
            'readyResults' => NdtResult::query()
                ->with(['weld.object.city', 'task.request', 'method', 'executorEmployee.object.city'])
                ->where('status', NdtResultStatus::ReadyForConclusion)
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas('weld', function ($subQuery) use ($objectId): void {
                        $subQuery->where('object_id', $objectId);
                    });
                })
                ->orderByDesc('control_date')
                ->orderByDesc('id')
                ->get(),
            'employees' => Employee::query()->with('object.city')->orderBy('last_name')->get(),
        ]);
    }

    public function show(Request $request, Conclusion $conclusion): View
    {
        $this->authorize('view', $conclusion);

        $conclusion->load([
            'object.city',
            'method',
            'request.object.city',
            'preparedBy.object.city',
            'checkedBy.object.city',
            'approvedBy.object.city',
            'items.result.weld.object.city',
            'versions.file.uploadedBy',
            'versions.createdBy',
            'files.uploadedBy',
            'statusHistory.changedBy',
            'latestVersion.file',
        ]);

        $user = $request->user();
        $objectId = $user?->objectId();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;

        return view('modules.conclusions.show', [
            'conclusion' => $conclusion,
            'statuses' => ConclusionStatus::options(),
            'readyResults' => NdtResult::query()
                ->with(['weld.object.city', 'task.request', 'method', 'executorEmployee.object.city'])
                ->where('status', NdtResultStatus::ReadyForConclusion)
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas('weld', function ($subQuery) use ($objectId): void {
                        $subQuery->where('object_id', $objectId);
                    });
                })
                ->orderByDesc('control_date')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(StoreConclusionRequest $request, ConclusionService $service): RedirectResponse
    {
        $conclusion = $service->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.conclusions.show', $conclusion)->with('status', 'Заключение создано.');
    }

    public function update(UpdateConclusionRequest $request, Conclusion $conclusion, ConclusionService $service): RedirectResponse
    {
        $service->update(
            conclusion: $conclusion,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заключение обновлено.');
    }

    public function submit(SubmitConclusionRequest $request, Conclusion $conclusion, ConclusionService $service): RedirectResponse
    {
        $service->submitForApproval(
            conclusion: $conclusion,
            actor: $request->user(),
            comment: $request->string('comment')->toString() !== '' ? $request->string('comment')->toString() : null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заключение отправлено на проверку.');
    }

    public function approve(ApproveConclusionRequest $request, Conclusion $conclusion, ConclusionService $service): RedirectResponse
    {
        $service->approve(
            conclusion: $conclusion,
            actor: $request->user(),
            comment: $request->string('comment')->toString() !== '' ? $request->string('comment')->toString() : null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заключение утверждено.');
    }

    public function returnForRevision(ApproveConclusionRequest $request, Conclusion $conclusion, ConclusionService $service): RedirectResponse
    {
        $service->returnForRevision(
            conclusion: $conclusion,
            actor: $request->user(),
            comment: $request->string('comment')->toString() !== '' ? $request->string('comment')->toString() : null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заключение возвращено на доработку.');
    }

    public function issue(IssueConclusionRequest $request, Conclusion $conclusion, ConclusionService $service): RedirectResponse
    {
        $service->issue(
            conclusion: $conclusion,
            basis: $request->string('basis')->toString() !== '' ? $request->string('basis')->toString() : 'Выдача заключения',
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заключение выдано.');
    }

    public function annul(AnnulConclusionRequest $request, Conclusion $conclusion, ConclusionService $service): RedirectResponse
    {
        $service->annul(
            conclusion: $conclusion,
            reason: $request->string('reason')->toString(),
            actor: $request->user(),
            comment: $request->string('comment')->toString() !== '' ? $request->string('comment')->toString() : null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заключение аннулировано.');
    }

    public function replace(ReplaceConclusionRequest $request, Conclusion $conclusion, ConclusionService $service): RedirectResponse
    {
        $replacement = $service->replace(
            conclusion: $conclusion,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.conclusions.show', $replacement)->with('status', 'Создано новое заключение на замену.');
    }

    public function storeVersion(StoreConclusionVersionRequest $request, Conclusion $conclusion): RedirectResponse
    {
        GenerateConclusionPdfJob::dispatch(
            conclusionId: $conclusion->getKey(),
            basis: $request->validated()['basis'],
            actorId: $request->user()?->getKey(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Версия заключения поставлена в очередь на генерацию.');
    }

    public function storeFile(StoreFileRequest $request, Conclusion $conclusion, FileService $fileService, ConclusionService $service): RedirectResponse
    {
        $this->authorize('manage', $conclusion);

        $file = $fileService->store(
            upload: $request->file('file'),
            actor: $request->user(),
            related: $conclusion,
        );

        $service->attachFile(
            conclusion: $conclusion,
            file: $file,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Файл прикреплен к заключению.');
    }
}
