<?php

declare(strict_types=1);

namespace App\Modules\NdtResults\DTO;

/**
 * Structured payload for creating and updating a result.
 */
final class NdtResultData
{
    public function __construct(
        public readonly int $ndtTaskId,
        public readonly int $weldId,
        public readonly int $ndtMethodId,
        public readonly ?int $executorEmployeeId,
        public readonly ?int $equipmentId,
        public readonly ?int $normativeDocumentId,
        public readonly string $controlDate,
        public readonly ?string $resultText,
        public readonly ?string $comment,
    ) {}

    /**
     * @param  array{
     *     ndt_task_id: int,
     *     weld_id: int,
     *     ndt_method_id: int,
     *     executor_employee_id?: int|null,
     *     equipment_id?: int|null,
     *     normative_document_id?: int|null,
     *     control_date: string,
     *     result_text?: string|null,
     *     comment?: string|null
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            ndtTaskId: $data['ndt_task_id'],
            weldId: $data['weld_id'],
            ndtMethodId: $data['ndt_method_id'],
            executorEmployeeId: $data['executor_employee_id'] ?? null,
            equipmentId: $data['equipment_id'] ?? null,
            normativeDocumentId: $data['normative_document_id'] ?? null,
            controlDate: $data['control_date'],
            resultText: $data['result_text'] ?? null,
            comment: $data['comment'] ?? null,
        );
    }
}
