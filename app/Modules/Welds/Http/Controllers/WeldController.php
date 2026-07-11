<?php

declare(strict_types=1);

namespace App\Modules\Welds\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Drawing;
use App\Modules\Admin\Models\Line;
use App\Modules\Admin\Models\Material;
use App\Modules\Admin\Models\Medium;
use App\Modules\Admin\Models\NormativeDocument;
use App\Modules\Admin\Models\PipelineCategory;
use App\Modules\Admin\Models\Title;
use App\Modules\Admin\Models\WeldingProcess;
use App\Modules\Admin\Models\WeldType;
use App\Modules\NdtTasks\Http\Requests\SyncWeldNdtMethodsRequest;
use App\Modules\NdtTasks\Models\NdtMethod;
use App\Modules\NdtTasks\Services\NdtTaskService;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Http\Requests\StoreWeldRequest;
use App\Modules\Welds\Http\Requests\UpdateWeldRequest;
use App\Modules\Welds\Http\Requests\UpdateWeldStatusRequest;
use App\Modules\Welds\Models\Weld;
use App\Modules\Welds\Services\WeldService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class WeldController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Weld::class);
        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $currentObject = $this->currentObject($user);
        $objectId = $currentObject?->getKey();

        $welds = Weld::query()
            ->with(['object.city', 'title', 'drawing', 'line', 'ndtMethods'])
            ->when(! $isAdmin, function ($query) use ($objectId): void {
                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where('weld_number', 'like', '%'.$search.'%');
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->where('object_id', (int) $request->input('object_id'));
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('modules.welds.index', [
            'welds' => $welds,
            'objects' => $isAdmin
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$currentObject])->filter(),
            'titles' => Title::query()->orderBy('name')->get(),
            'drawings' => Drawing::query()->orderBy('name')->get(),
            'lines' => Line::query()->orderBy('name')->get(),
            'materials' => Material::query()->orderBy('name')->get(),
            'weldingProcesses' => WeldingProcess::query()->orderBy('name')->get(),
            'weldTypes' => WeldType::query()->orderBy('name')->get(),
            'pipelineCategories' => PipelineCategory::query()->orderBy('name')->get(),
            'media' => Medium::query()->orderBy('name')->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Weld::class);
        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $currentObject = $this->currentObject($user);

        return view('modules.welds.create', [
            'isAdmin' => $isAdmin,
            'currentObject' => $currentObject,
            'objects' => $isAdmin
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$currentObject])->filter(),
            'titles' => Title::query()->orderBy('name')->get(),
            'drawings' => Drawing::query()->orderBy('name')->get(),
            'lines' => Line::query()->orderBy('name')->get(),
            'materials' => Material::query()->orderBy('name')->get(),
            'weldingProcesses' => WeldingProcess::query()->orderBy('name')->get(),
            'weldTypes' => WeldType::query()->orderBy('name')->get(),
            'pipelineCategories' => PipelineCategory::query()->orderBy('name')->get(),
            'media' => Medium::query()->orderBy('name')->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Weld $weld): View
    {
        $this->authorize('view', $weld);
        $user = request()->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $currentObject = $this->currentObject($user);

        $weld->load(['object.city', 'title', 'drawing', 'line', 'material1', 'material2', 'weldingProcess', 'weldType', 'pipelineCategory', 'medium', 'normativeDocument', 'ndtMethods', 'statusHistory.changedBy']);

        return view('modules.welds.show', [
            'weld' => $weld,
            'isAdmin' => $isAdmin,
            'currentObject' => $currentObject,
            'objects' => $isAdmin
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$currentObject])->filter(),
            'titles' => Title::query()->orderBy('name')->get(),
            'drawings' => Drawing::query()->orderBy('name')->get(),
            'lines' => Line::query()->orderBy('name')->get(),
            'materials' => Material::query()->orderBy('name')->get(),
            'weldingProcesses' => WeldingProcess::query()->orderBy('name')->get(),
            'weldTypes' => WeldType::query()->orderBy('name')->get(),
            'pipelineCategories' => PipelineCategory::query()->orderBy('name')->get(),
            'media' => Medium::query()->orderBy('name')->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function edit(Weld $weld): View
    {
        $this->authorize('manage', $weld);
        $user = request()->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $currentObject = $this->currentObject($user);

        $weld->load(['object.city', 'title', 'drawing', 'line', 'material1', 'material2', 'weldingProcess', 'weldType', 'pipelineCategory', 'medium', 'normativeDocument', 'ndtMethods', 'statusHistory.changedBy']);

        return view('modules.welds.edit', [
            'weld' => $weld,
            'isAdmin' => $isAdmin,
            'currentObject' => $currentObject,
            'objects' => $isAdmin
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$currentObject])->filter(),
            'titles' => Title::query()->orderBy('name')->get(),
            'drawings' => Drawing::query()->orderBy('name')->get(),
            'lines' => Line::query()->orderBy('name')->get(),
            'materials' => Material::query()->orderBy('name')->get(),
            'weldingProcesses' => WeldingProcess::query()->orderBy('name')->get(),
            'weldTypes' => WeldType::query()->orderBy('name')->get(),
            'pipelineCategories' => PipelineCategory::query()->orderBy('name')->get(),
            'media' => Medium::query()->orderBy('name')->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
            'methods' => NdtMethod::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => WeldStatus::options(),
        ]);
    }

    public function store(StoreWeldRequest $request, WeldService $welds): RedirectResponse
    {
        $weld = $welds->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.welds.show', $weld)->with('status', 'Стык создан.');
    }

    public function update(UpdateWeldRequest $request, Weld $weld, WeldService $welds): RedirectResponse
    {
        $this->authorize('manage', $weld);

        $updated = $welds->update(
            weld: $weld,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.welds.show', $updated)->with('status', 'Стык обновлен.');
    }

    public function updateStatus(UpdateWeldStatusRequest $request, Weld $weld, WeldService $welds): RedirectResponse
    {
        $this->authorize('manage', $weld);

        $welds->updateStatus(
            weld: $weld,
            status: WeldStatus::from($request->validated('status')),
            comment: $request->validated('comment') ?? null,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Статус стыка обновлен.');
    }

    public function syncMethods(SyncWeldNdtMethodsRequest $request, Weld $weld, NdtTaskService $tasks): RedirectResponse
    {
        $this->authorize('manage', $weld);
        $this->authorize('weld_ndt_methods.manage');

        $tasks->syncWeldMethods(
            weld: $weld,
            methodIds: array_map(static fn (int|string $methodId): int => (int) $methodId, $request->validated('method_ids')),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Методы контроля стыка обновлены.');
    }

    private function currentObject(?\App\Models\User $user): ?NdtObject
    {
        $objectId = $user?->objectId();
        if ($objectId === null) {
            return null;
        }

        return NdtObject::query()->with('city')->find($objectId);
    }
}
