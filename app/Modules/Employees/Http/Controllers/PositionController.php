<?php

declare(strict_types=1);

namespace App\Modules\Employees\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Employees\Http\Requests\StorePositionRequest;
use App\Modules\Employees\Http\Requests\UpdatePositionRequest;
use App\Modules\Employees\Models\Position;
use App\Modules\Employees\Services\PositionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PositionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('positions.manage');

        $positions = Position::query()
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $query->where('name', 'like', '%'.$request->string('search')->toString().'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.employees.positions.index', [
            'positions' => $positions,
        ]);
    }

    public function store(StorePositionRequest $request, PositionService $positions): RedirectResponse
    {
        $this->authorize('positions.manage');

        $positions->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Должность создана.');
    }

    public function update(UpdatePositionRequest $request, Position $position, PositionService $positions): RedirectResponse
    {
        $this->authorize('positions.manage');

        $positions->update(
            position: $position,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Должность обновлена.');
    }

    public function destroy(Request $request, Position $position, PositionService $positions): RedirectResponse
    {
        $this->authorize('positions.manage');

        $positions->deactivate(
            position: $position,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Должность деактивирована.');
    }
}
