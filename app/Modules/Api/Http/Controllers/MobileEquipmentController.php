<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Modules\Api\Http\Resources\EquipmentResource;
use App\Modules\Equipment\Enums\EquipmentStatus;
use App\Modules\Equipment\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MobileEquipmentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Equipment::class);

        $query = Equipment::query()
            ->with(['type', 'object.city', 'latestVerification', 'latestCalibration'])
            ->when(! $request->user()?->hasRole('Администратор системы'), function ($query) use ($request): void {
                $objectId = $request->user()?->objectId();

                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
            })
            ->when($request->boolean('available', true), fn ($query) => $query->whereIn('status', [
                EquipmentStatus::Available->value,
                EquipmentStatus::Issued->value,
            ]))
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', '%'.$search.'%')
                        ->orWhere('inventory_number', 'like', '%'.$search.'%')
                        ->orWhere('serial_number', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('equipment_type_id'), fn ($query) => $query->where('equipment_type_id', (int) $request->input('equipment_type_id')))
            ->orderBy('name');

        return $this->paginated(
            $query->paginate((int) $request->input('per_page', 15))->withQueryString(),
            static fn (Equipment $equipment): EquipmentResource => new EquipmentResource($equipment),
        );
    }
}
