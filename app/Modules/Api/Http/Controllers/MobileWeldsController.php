<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Modules\Api\Http\Resources\NdtResultResource;
use App\Modules\Api\Http\Resources\WeldResource;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\Welds\Models\Weld;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MobileWeldsController extends ApiController
{
    public function show(Weld $weld): JsonResponse
    {
        $this->authorize('view', $weld);

        $weld->load(['object.city', 'ndtMethods']);

        return $this->success(new WeldResource($weld));
    }

    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Weld::class);

        $query = Weld::query()
            ->with(['object.city', 'ndtMethods'])
            ->when($request->string('q')->toString() !== '', function ($query) use ($request): void {
                $query->where('weld_number', 'like', '%'.$request->string('q')->toString().'%');
            })
            ->when($request->filled('object_id'), fn ($query) => $query->where('object_id', (int) $request->input('object_id')))
            ->orderByDesc('id');

        return $this->paginated(
            $query->paginate((int) $request->input('per_page', 15))->withQueryString(),
            static fn (Weld $weld): WeldResource => new WeldResource($weld),
        );
    }

    public function results(Request $request, Weld $weld): JsonResponse
    {
        $this->authorize('view', $weld);

        $results = NdtResult::query()
            ->with(['weld.object.city', 'method'])
            ->where('weld_id', $weld->getKey())
            ->orderByDesc('control_date')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15))
            ->withQueryString();

        return $this->paginated(
            $results,
            static fn (NdtResult $result): NdtResultResource => new NdtResultResource($result),
        );
    }
}
