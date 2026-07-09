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
use App\Modules\Admin\Models\WeldType;
use App\Modules\Admin\Models\WeldingProcess;
use App\Modules\Employees\Models\Employee;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Welds\Enums\WeldStatus;
use App\Modules\Welds\Http\Requests\StoreWeldRequest;
use App\Modules\Welds\Http\Requests\StoreWeldWelderRequest;
use App\Modules\Welds\Http\Requests\StoreWelderRequest;
use App\Modules\Welds\Http\Requests\UpdateWeldRequest;
use App\Modules\Welds\Http\Requests\UpdateWeldStatusRequest;
use App\Modules\Welds\Http\Requests\UpdateWelderRequest;
use App\Modules\Welds\Models\Weld;
use App\Modules\Welds\Models\Welder;
use App\Modules\Welds\Services\WeldService;
use App\Modules\Welds\Services\WelderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class WeldController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Weld::class);

        $welds = Weld::query()
            ->with(['object.city', 'title', 'drawing', 'line', 'welders'])
            ->when(! $request->user()->can('welds.manage') && $request->user() !== null, function ($query) use ($request): void {
                $query->where('object_id', $request->user()->objectId());
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
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
            'drawings' => Drawing::query()->orderBy('name')->get(),
            'lines' => Line::query()->orderBy('name')->get(),
            'materials' => Material::query()->orderBy('name')->get(),
            'weldingProcesses' => WeldingProcess::query()->orderBy('name')->get(),
            'weldTypes' => WeldType::query()->orderBy('name')->get(),
            'pipelineCategories' => PipelineCategory::query()->orderBy('name')->get(),
            'media' => Medium::query()->orderBy('name')->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
            'welders' => Welder::query()->with('employee')->orderBy('name')->get(),
            'employeeOptions' => Employee::query()->with(['object.city'])->orderBy('last_name')->get(),
        ]);
    }

    public function show(Weld $weld): View
    {
        $this->authorize('view', $weld);

        $weld->load(['object.city', 'title', 'drawing', 'line', 'material1', 'material2', 'weldingProcess', 'weldType', 'pipelineCategory', 'medium', 'normativeDocument', 'welders.employee', 'statusHistory.changedBy']);

        return view('modules.welds.show', [
            'weld' => $weld,
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
            'drawings' => Drawing::query()->orderBy('name')->get(),
            'lines' => Line::query()->orderBy('name')->get(),
            'materials' => Material::query()->orderBy('name')->get(),
            'weldingProcesses' => WeldingProcess::query()->orderBy('name')->get(),
            'weldTypes' => WeldType::query()->orderBy('name')->get(),
            'pipelineCategories' => PipelineCategory::query()->orderBy('name')->get(),
            'media' => Medium::query()->orderBy('name')->get(),
            'normativeDocuments' => NormativeDocument::query()->orderBy('name')->get(),
            'welders' => Welder::query()->with('employee')->orderBy('name')->get(),
            'employeeOptions' => Employee::query()->with(['object.city'])->orderBy('last_name')->get(),
        ]);
    }

    public function store(StoreWeldRequest $request, WeldService $welds): RedirectResponse
    {
        $welds->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Стык создан.');
    }

    public function update(UpdateWeldRequest $request, Weld $weld, WeldService $welds): RedirectResponse
    {
        $this->authorize('manage', $weld);

        $welds->update(
            weld: $weld,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Стык обновлен.');
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

    public function attachWelder(StoreWeldWelderRequest $request, Weld $weld, WeldService $welds): RedirectResponse
    {
        $this->authorize('manage', $weld);

        $welder = Welder::query()->findOrFail((int) $request->validated('welder_id'));

        $welds->attachWelder(
            weld: $weld,
            welder: $welder,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Сварщик добавлен к стыку.');
    }

    public function detachWelder(Request $request, Weld $weld, Welder $welder, WeldService $welds): RedirectResponse
    {
        $this->authorize('manage', $weld);

        $welds->detachWelder(
            weld: $weld,
            welder: $welder,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Сварщик удален из стыка.');
    }

    public function storeWelder(StoreWelderRequest $request, WelderService $welders): RedirectResponse
    {
        $welders->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Сварщик создан.');
    }

    public function updateWelder(UpdateWelderRequest $request, Welder $welder, WelderService $welders): RedirectResponse
    {
        $this->authorize('manage', $welder);

        $welders->update(
            welder: $welder,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Сварщик обновлен.');
    }

    public function destroyWelder(Request $request, Welder $welder, WelderService $welders): RedirectResponse
    {
        $this->authorize('manage', $welder);

        $welders->deactivate(
            welder: $welder,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Сварщик деактивирован.');
    }
}
