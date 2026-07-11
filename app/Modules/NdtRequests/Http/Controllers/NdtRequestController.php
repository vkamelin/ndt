<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Title;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Http\Requests\StoreNdtRequestItemRequest;
use App\Modules\NdtRequests\Http\Requests\StoreNdtRequestRequest;
use App\Modules\NdtRequests\Http\Requests\UpdateNdtRequestRequest;
use App\Modules\NdtRequests\Http\Requests\UpdateNdtRequestStatusRequest;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtRequests\Services\NdtRequestService;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Welds\Models\Weld;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class NdtRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', NdtRequest::class);

        $requests = NdtRequest::query()
            ->with(['organization', 'object.city', 'title', 'welds'])
            ->when(! ($request->user()?->hasRole('Администратор системы') ?? false), function ($query) use ($request): void {
                $objectId = $request->user()?->objectId();

                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $query->where('request_number', 'like', '%'.$request->string('search')->toString().'%');
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

        return view('modules.ndt-requests.index', [
            'requests' => $requests,
            'organizations' => Organization::query()->orderBy('name')->get(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
            'statuses' => NdtRequestStatus::options(),
        ]);
    }

    public function show(NdtRequest $ndtRequest): View
    {
        $this->authorize('view', $ndtRequest);

        $ndtRequest->load(['organization', 'object.city', 'title', 'welds.object.city', 'statusHistory.changedBy']);

        return view('modules.ndt-requests.show', [
            'request' => $ndtRequest,
            'organizations' => Organization::query()->orderBy('name')->get(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
            'welds' => Weld::query()
                ->with(['object.city'])
                ->where('object_id', $ndtRequest->object_id)
                ->orderByDesc('id')
                ->get(),
            'statuses' => NdtRequestStatus::options(),
        ]);
    }

    public function store(StoreNdtRequestRequest $request, NdtRequestService $ndtRequests): RedirectResponse
    {
        $ndtRequests->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заявка создана.');
    }

    public function update(UpdateNdtRequestRequest $request, NdtRequest $ndtRequest, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $ndtRequests->update(
            request: $ndtRequest,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заявка обновлена.');
    }

    public function updateStatus(UpdateNdtRequestStatusRequest $request, NdtRequest $ndtRequest, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $ndtRequests->updateStatus(
            request: $ndtRequest,
            status: NdtRequestStatus::from($request->validated('status')),
            comment: $request->validated('comment') ?? null,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Статус заявки обновлен.');
    }

    public function attachWeld(StoreNdtRequestItemRequest $request, NdtRequest $ndtRequest, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $weld = Weld::query()->findOrFail((int) $request->validated('weld_id'));

        $ndtRequests->attachWeld(
            request: $ndtRequest,
            weld: $weld,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Стык добавлен в заявку.');
    }

    public function detachWeld(Request $request, NdtRequest $ndtRequest, Weld $weld, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $ndtRequests->detachWeld(
            request: $ndtRequest,
            weld: $weld,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Стык удален из заявки.');
    }
}
