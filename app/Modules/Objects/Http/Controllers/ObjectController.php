<?php

declare(strict_types=1);

namespace App\Modules\Objects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Objects\Http\Requests\StoreObjectRequest;
use App\Modules\Objects\Http\Requests\UpdateObjectRequest;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Objects\Services\ObjectService;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ObjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', NdtObject::class);

        $objects = NdtObject::query()
            ->with(['city'])
            ->when(! $request->user()->can('objects.manage') && $request->user() !== null, function ($query) use ($request): void {
                $query->where('id', $request->user()->objectId());
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $query->where('name', 'like', '%'.$request->string('search')->toString().'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.objects.objects.index', [
            'objects' => $objects,
            'cities' => City::query()->orderBy('name')->get(),
            'organizations' => Organization::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreObjectRequest $request, ObjectService $objects): RedirectResponse
    {
        $this->authorize('objects.manage');

        $objects->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Объект/участок создан.');
    }

    public function update(UpdateObjectRequest $request, NdtObject $object, ObjectService $objects): RedirectResponse
    {
        $this->authorize('objects.manage');

        $objects->update(
            object: $object,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Объект/участок обновлен.');
    }

    public function destroy(Request $request, NdtObject $object, ObjectService $objects): RedirectResponse
    {
        $this->authorize('objects.manage');

        $objects->deactivate(
            object: $object,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Объект/участок деактивирован.');
    }
}
