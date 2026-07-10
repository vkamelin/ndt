<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Inventory\Models\ChemicalInventoryTransaction;
use App\Modules\Inventory\Models\ChemicalRequest;
use App\Modules\Inventory\Models\FilmInventoryTransaction;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Support\Facades\DB;

final class InventoryService
{
    use RecordsAuditLogs;

    /**
     * @param  array{rt_film_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function recordFilmTransaction(Shift $shift, string $operation, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): FilmInventoryTransaction
    {
        return DB::transaction(function () use ($shift, $operation, $data, $actor, $ipAddress, $userAgent): FilmInventoryTransaction {
            $transaction = $shift->filmTransactions()->create([
                'rt_film_id' => $data['rt_film_id'] ?? null,
                'operation' => $operation,
                'quantity' => $data['quantity'] ?? 1,
                'transacted_at' => $data['transacted_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: FilmInventoryTransaction::class,
                    entityId: $transaction->getKey(),
                    operation: 'film_inventory_transaction.created',
                    after: [
                        'id' => $transaction->getKey(),
                        'shift_id' => $transaction->shift_id,
                        'operation' => $transaction->operation,
                        'quantity' => $transaction->quantity,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $transaction;
        });
    }

    /**
     * @param  array{chemical_type_id?: int|null, quantity?: int|null, transacted_at?: string|null, comment?: string|null}  $data
     */
    public function recordChemicalTransaction(Shift $shift, string $operation, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ChemicalInventoryTransaction
    {
        return DB::transaction(function () use ($shift, $operation, $data, $actor, $ipAddress, $userAgent): ChemicalInventoryTransaction {
            $transaction = $shift->chemicalTransactions()->create([
                'chemical_type_id' => $data['chemical_type_id'] ?? null,
                'operation' => $operation,
                'quantity' => $data['quantity'] ?? 1,
                'transacted_at' => $data['transacted_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: ChemicalInventoryTransaction::class,
                    entityId: $transaction->getKey(),
                    operation: 'chemical_inventory_transaction.created',
                    after: [
                        'id' => $transaction->getKey(),
                        'shift_id' => $transaction->shift_id,
                        'operation' => $transaction->operation,
                        'quantity' => $transaction->quantity,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $transaction;
        });
    }

    /**
     * @param  array{chemical_type_id?: int|null, quantity?: int|null, requested_at?: string|null, comment?: string|null}  $data
     */
    public function requestChemical(Shift $shift, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): ChemicalRequest
    {
        return DB::transaction(function () use ($shift, $data, $actor, $ipAddress, $userAgent): ChemicalRequest {
            $request = $shift->chemicalRequests()->create([
                'chemical_type_id' => $data['chemical_type_id'] ?? null,
                'quantity' => $data['quantity'] ?? 1,
                'status' => 'requested',
                'requested_at' => $data['requested_at'] ?? now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->recordAudit(
                AuditData::forModelChange(
                    entityType: ChemicalRequest::class,
                    entityId: $request->getKey(),
                    operation: 'chemical_request.created',
                    after: [
                        'id' => $request->getKey(),
                        'shift_id' => $request->shift_id,
                        'quantity' => $request->quantity,
                        'status' => $request->status,
                    ],
                    actor: $actor,
                    ipAddress: $ipAddress,
                    userAgent: $userAgent,
                ),
            );

            return $request;
        });
    }
}
