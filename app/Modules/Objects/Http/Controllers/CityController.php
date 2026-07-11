<?php

declare(strict_types=1);

namespace App\Modules\Objects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Objects\Http\Requests\StoreCityRequest;
use App\Modules\Objects\Http\Requests\UpdateCityRequest;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Services\CityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', City::class);

        $cities = City::query()
            ->withCount('objects')
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $query->where('name', 'like', '%'.$request->string('search')->toString().'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.objects.cities.index', [
            'cities' => $cities,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $this->authorize('cities.manage');

        return redirect()->route('admin.cities.index');
    }

    public function edit(Request $request, City $city): RedirectResponse
    {
        $this->authorize('cities.manage');

        return redirect()->route('admin.cities.index');
    }

    public function store(StoreCityRequest $request, CityService $cities): RedirectResponse
    {
        $this->authorize('cities.manage');

        $cities->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Город создан.');
    }

    public function update(UpdateCityRequest $request, City $city, CityService $cities): RedirectResponse
    {
        $this->authorize('cities.manage');

        $cities->update(
            city: $city,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Город обновлен.');
    }

    public function destroy(Request $request, City $city, CityService $cities): RedirectResponse
    {
        $this->authorize('cities.manage');

        $cities->deactivate(
            city: $city,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Город деактивирован.');
    }
}
