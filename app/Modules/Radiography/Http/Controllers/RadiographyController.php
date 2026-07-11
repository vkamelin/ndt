<?php

declare(strict_types=1);

namespace App\Modules\Radiography\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\FilmType;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\Radiography\Enums\RtStatus;
use App\Modules\Radiography\Http\Requests\StoreRtArchiveItemRequest;
use App\Modules\Radiography\Http\Requests\StoreRtDensityMeasurementRequest;
use App\Modules\Radiography\Http\Requests\StoreRtExposureRequest;
use App\Modules\Radiography\Http\Requests\StoreRtFilmRequest;
use App\Modules\Radiography\Http\Requests\StoreRtImageRequest;
use App\Modules\Radiography\Http\Requests\StoreRtReshootRequest;
use App\Modules\Radiography\Http\Requests\StoreRtResultRequest;
use App\Modules\Radiography\Http\Requests\UpdateRtStatusRequest;
use App\Modules\Radiography\Models\RtFilm;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Radiography\Services\RadiographyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RadiographyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RtResult::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        $results = RtResult::query()
            ->with(['ndtResult.weld.object.city', 'filmType', 'films.filmType', 'densityMeasurements', 'reshoots', 'archiveItems'])
            ->when(! $isAdmin, function ($query) use ($objectId): void {
                $query->whereHas('ndtResult.weld', function ($subQuery) use ($objectId): void {
                    if ($objectId === null) {
                        $subQuery->whereRaw('1 = 0');

                        return;
                    }

                    $subQuery->where('object_id', $objectId);
                });
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('barcode', 'like', '%'.$search.'%')
                        ->orWhere('conclusion_number', 'like', '%'.$search.'%')
                        ->orWhereHas('ndtResult.weld', function ($weldQuery) use ($search): void {
                            $weldQuery->where('weld_number', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            });

        return view('modules.radiography.index', [
            'results' => $results->orderByDesc('id')->paginate(15)->withQueryString(),
            'statuses' => RtStatus::options(),
            'filmTypes' => FilmType::query()->where('is_active', true)->orderBy('name')->get(),
            'ndtResults' => NdtResult::query()
                ->with(['weld.object.city', 'method'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas('weld', function ($weldQuery) use ($objectId): void {
                        $weldQuery->where('object_id', $objectId);
                    });
                })
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RtResult::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        return view('modules.radiography.create', [
            'ndtResults' => NdtResult::query()
                ->with(['weld.object.city', 'method'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas('weld', function ($weldQuery) use ($objectId): void {
                        $weldQuery->where('object_id', $objectId);
                    });
                })
                ->orderByDesc('id')
                ->get(),
            'filmTypes' => FilmType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, RtResult $rtResult): View
    {
        $this->authorize('view', $rtResult);

        $rtResult->load([
            'ndtResult.weld.object.city',
            'filmType',
            'films.filmType',
            'films.images.file',
            'films.exposures',
            'exposures',
            'reshoots',
            'densityMeasurements',
            'archiveItems.file',
            'latestArchiveItem',
        ]);

        return view('modules.radiography.show', [
            'result' => $rtResult,
            'statuses' => RtStatus::options(),
            'filmTypes' => FilmType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function edit(Request $request, RtResult $rtResult): View
    {
        $this->authorize('manage', $rtResult);
        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $objectId = $user?->objectId();

        $rtResult->load([
            'ndtResult.weld.object.city',
            'filmType',
            'films.filmType',
            'films.images.file',
            'films.exposures',
            'exposures',
            'reshoots',
            'densityMeasurements',
            'archiveItems.file',
            'latestArchiveItem',
        ]);

        return view('modules.radiography.edit', [
            'result' => $rtResult,
            'ndtResults' => NdtResult::query()
                ->with(['weld.object.city', 'method'])
                ->when(! $isAdmin, function ($query) use ($objectId): void {
                    if ($objectId === null) {
                        $query->whereRaw('1 = 0');

                        return;
                    }

                    $query->whereHas('weld', function ($weldQuery) use ($objectId): void {
                        $weldQuery->where('object_id', $objectId);
                    });
                })
                ->orderByDesc('id')
                ->get(),
            'statuses' => RtStatus::options(),
            'filmTypes' => FilmType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRtResultRequest $request, RadiographyService $radiography): RedirectResponse
    {
        $ndtResult = NdtResult::query()->with('weld')->findOrFail((int) $request->validated('ndt_result_id'));
        $this->authorize('create', [RtResult::class, $ndtResult]);

        $rtResult = $radiography->createOrUpdate(
            ndtResult: $ndtResult,
            data: [
                'film_type_id' => $request->validated('film_type_id') !== null ? (int) $request->validated('film_type_id') : null,
                'barcode' => $request->validated('barcode') ?? null,
                'conclusion_number' => $request->validated('conclusion_number') ?? null,
                'control_date' => $request->validated('control_date') ?? null,
                'conclusion_date' => $request->validated('conclusion_date') ?? null,
                'archive_location' => $request->validated('archive_location') ?? null,
                'result_text' => $request->validated('result_text') ?? null,
                'comment' => $request->validated('comment') ?? null,
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.radiography.show', $rtResult)->with('status', 'Карта РК сохранена.');
    }

    public function storeFilm(StoreRtFilmRequest $request, RtResult $rtResult, RadiographyService $radiography): RedirectResponse
    {
        $this->authorize('manage', $rtResult);

        $radiography->addFilm(
            result: $rtResult,
            data: [
                'film_type_id' => $request->validated('film_type_id') !== null ? (int) $request->validated('film_type_id') : null,
                'barcode' => $request->validated('barcode') ?? null,
                'position_number' => $request->validated('position_number') !== null ? (int) $request->validated('position_number') : null,
                'comment' => $request->validated('comment') ?? null,
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Пленка добавлена.');
    }

    public function storeImage(StoreRtImageRequest $request, RtFilm $rtFilm, RadiographyService $radiography): RedirectResponse
    {
        $this->authorize('manage', $rtFilm->result);

        $radiography->addImage(
            film: $rtFilm,
            data: [
                'file_id' => $request->validated('file_id') !== null ? (int) $request->validated('file_id') : null,
                'sequence_number' => $request->validated('sequence_number') !== null ? (int) $request->validated('sequence_number') : null,
                'captured_at' => $request->validated('captured_at') ?? null,
                'comment' => $request->validated('comment') ?? null,
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Снимок добавлен.');
    }

    public function storeExposure(StoreRtExposureRequest $request, RtFilm $rtFilm, RadiographyService $radiography): RedirectResponse
    {
        $this->authorize('manage', $rtFilm->result);

        $radiography->addExposure(
            film: $rtFilm,
            data: [
                'rt_result_id' => (int) $request->validated('rt_result_id') ?: $rtFilm->rt_result_id,
                'exposure_number' => $request->validated('exposure_number') !== null ? (int) $request->validated('exposure_number') : null,
                'exposed_at' => $request->validated('exposed_at') ?? null,
                'comment' => $request->validated('comment') ?? null,
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Экспозиция добавлена.');
    }

    public function storeReshoot(StoreRtReshootRequest $request, RtResult $rtResult, RadiographyService $radiography): RedirectResponse
    {
        $this->authorize('manage', $rtResult);

        $radiography->addReshoot(
            result: $rtResult,
            data: [
                'rt_film_id' => $request->validated('rt_film_id') !== null ? (int) $request->validated('rt_film_id') : null,
                'reason' => $request->validated('reason'),
                'reshot_at' => $request->validated('reshot_at') ?? null,
                'comment' => $request->validated('comment') ?? null,
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Пересвет зафиксирован.');
    }

    public function storeDensity(StoreRtDensityMeasurementRequest $request, RtResult $rtResult, RadiographyService $radiography): RedirectResponse
    {
        $this->authorize('manage', $rtResult);

        $radiography->addDensityMeasurement(
            result: $rtResult,
            data: [
                'rt_film_id' => $request->validated('rt_film_id') !== null ? (int) $request->validated('rt_film_id') : null,
                'density' => $request->validated('density') !== null ? $request->validated('density') : null,
                'minimum_density' => $request->validated('minimum_density') !== null ? $request->validated('minimum_density') : null,
                'maximum_density' => $request->validated('maximum_density') !== null ? $request->validated('maximum_density') : null,
                'measured_at' => $request->validated('measured_at') ?? null,
                'comment' => $request->validated('comment') ?? null,
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Плотность зафиксирована.');
    }

    public function storeArchiveItem(StoreRtArchiveItemRequest $request, RtResult $rtResult, RadiographyService $radiography): RedirectResponse
    {
        $this->authorize('manage', $rtResult);

        $radiography->addArchiveItem(
            result: $rtResult,
            data: [
                'rt_film_id' => $request->validated('rt_film_id') !== null ? (int) $request->validated('rt_film_id') : null,
                'file_id' => $request->validated('file_id') !== null ? (int) $request->validated('file_id') : null,
                'register_number' => $request->validated('register_number') ?? null,
                'archive_location' => $request->validated('archive_location') ?? null,
                'archived_at' => $request->validated('archived_at') ?? null,
                'comment' => $request->validated('comment') ?? null,
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Архивная позиция добавлена.');
    }

    public function updateStatus(UpdateRtStatusRequest $request, RtResult $rtResult, RadiographyService $radiography): RedirectResponse
    {
        $this->authorize('transition', $rtResult);

        $status = RtStatus::from($request->validated('status'));

        match ($status) {
            RtStatus::LabTransferred => $radiography->transferToLab($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::Processing => $radiography->markProcessing($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::ReadyForDecoding => $radiography->markReadyForDecoding($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::Decoding => $radiography->startDecoding($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::ReshootDone => $radiography->markReshootDone($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::Decoded => $radiography->markDecoded($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::SentToAnalysis => $radiography->sendToAnalysis($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::IncludedInConclusion => $radiography->includeInConclusion($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            RtStatus::Archived => $radiography->archive($rtResult, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
            default => $radiography->changeStatus($rtResult, $status, $request->user(), $request->validated('comment') ?? null, $request->ip(), $request->userAgent()),
        };

        return back()->with('status', 'Статус РК обновлен.');
    }
}
