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
        $objectId = $user?->objectId();

        $reportJobs = ReportJob::query()
            ->with(['requestedBy', 'city', 'object', 'file'])
            ->when(! $user?->hasRole('Администратор системы'), function ($query) use ($objectId): void {
                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
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

        $cities = City::query()->orderBy('name');
        $objects = NdtObject::query()->with('city')->orderBy('name');

        if (! $user?->hasRole('Администратор системы') && $objectId !== null) {
            $cities->whereHas('objects', fn ($query) => $query->where('id', $objectId));
            $objects->where('id', $objectId);
        }

        return view('modules.reports.index', [
            'reportJobs' => $reportJobs->orderByDesc('id')->paginate(15)->withQueryString(),
            'reportTypes' => ReportType::cases(),
            'statuses' => ReportStatus::options(),
            'cities' => $cities->get(),
            'objects' => $objects->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', ReportJob::class);

        $user = $request->user();
        $objectId = $user?->objectId();

        $cities = City::query()->orderBy('name');
        $objects = NdtObject::query()->with('city')->orderBy('name');

        if (! $user?->hasRole('Администратор системы') && $objectId !== null) {
            $cities->whereHas('objects', fn ($query) => $query->where('id', $objectId));
            $objects->where('id', $objectId);
        }

        return view('modules.reports.create', [
            'reportTypes' => ReportType::cases(),
            'statuses' => ReportStatus::options(),
            'cities' => $cities->get(),
            'objects' => $objects->get(),
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
}
