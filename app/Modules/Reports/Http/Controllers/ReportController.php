<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Reports\Enums\ReportStatus;
use App\Modules\Reports\Enums\ReportType;
use App\Modules\Reports\Http\Requests\StoreReportRequest;
use App\Modules\Reports\Models\ReportJob;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ReportJob::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $scopeObject = $this->scopeObject($user);
        $scopeCity = $scopeObject?->city;

        $reportJobs = ReportJob::query()
            ->with(['requestedBy', 'city', 'object', 'file'])
            ->when(! $isAdmin, function ($query) use ($scopeObject): void {
                if ($scopeObject === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $scopeObject->getKey());
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('report_type'), function ($query) use ($request): void {
                $query->where('report_type', $request->string('report_type')->toString());
            })
            ->when($request->filled('city_id'), function ($query) use ($request): void {
                $query->where('city_id', (int) $request->input('city_id'));
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->where('object_id', (int) $request->input('object_id'));
            });

        $cities = $isAdmin
            ? City::query()->orderBy('name')->get()
            : collect([$scopeCity])->filter();
        $objects = $isAdmin
            ? NdtObject::query()->with('city')->orderBy('name')->get()
            : collect([$scopeObject])->filter();

        return view('modules.reports.index', [
            'reportJobs' => $reportJobs->orderByDesc('id')->paginate(15)->withQueryString(),
            'reportTypes' => ReportType::cases(),
            'statuses' => ReportStatus::options(),
            'cities' => $cities,
            'objects' => $objects,
            'isAdmin' => $isAdmin,
            'scopeCity' => $scopeCity,
            'scopeObject' => $scopeObject,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ReportJob::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $scopeObject = $this->scopeObject($user);
        $scopeCity = $scopeObject?->city;

        $cities = $isAdmin
            ? City::query()->orderBy('name')->get()
            : collect([$scopeCity])->filter();
        $objects = $isAdmin
            ? NdtObject::query()->with('city')->orderBy('name')->get()
            : collect([$scopeObject])->filter();

        return view('modules.reports.create', [
            'reportTypes' => ReportType::cases(),
            'statuses' => ReportStatus::options(),
            'cities' => $cities,
            'objects' => $objects,
            'isAdmin' => $isAdmin,
            'scopeCity' => $scopeCity,
            'scopeObject' => $scopeObject,
        ]);
    }

    public function store(StoreReportRequest $request, ReportService $service): RedirectResponse
    {
        $reportJob = $service->queue(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()
            ->route('admin.reports.index')
            ->withInput()
            ->with('status', 'Отчет "'.$reportJob->title.'" поставлен в очередь.');
    }

    private function scopeObject(?\App\Models\User $user): ?NdtObject
    {
        if ($user === null || $user->hasRole('Администратор системы')) {
            return null;
        }

        $objectId = $user->objectId();
        if ($objectId === null) {
            return null;
        }

        return NdtObject::query()->with('city')->find($objectId);
    }
}
