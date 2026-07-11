<?php

declare(strict_types=1);

namespace App\Modules\Reports\Services;

use App\Models\User;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Documents\Enums\FileStatus;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\File;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtResults\Enums\NdtResultStatus;
use App\Modules\NdtResults\Models\NdtResult;
use App\Modules\NdtTasks\Models\NdtTask;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Radiography\Models\RtResult;
use App\Modules\Registers\Models\Act;
use App\Modules\Registers\Models\TransferRegister;
use App\Modules\Reports\Enums\ReportFormat;
use App\Modules\Reports\Enums\ReportStatus;
use App\Modules\Reports\Enums\ReportType;
use App\Modules\Reports\Exports\ExcelReportWriter;
use App\Modules\Reports\Exports\PdfReportWriter;
use App\Modules\Reports\Jobs\ExportExcelJob;
use App\Modules\Reports\Jobs\GeneratePdfJob;
use App\Modules\Reports\Models\ReportJob;
use App\Modules\Shifts\Enums\ShiftType;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Welds\Models\Weld;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class ReportService
{
    public function __construct(
        private readonly ExcelReportWriter $excelWriter,
        private readonly PdfReportWriter $pdfWriter,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function queue(array $data, User $actor, ?string $ipAddress = null, ?string $userAgent = null): ReportJob
    {
        return DB::transaction(function () use ($data, $actor): ReportJob {
            $reportType = ReportType::from((string) $data['report_type']);
            $entity = $this->resolveEntity($reportType, $data);

            $reportJob = ReportJob::query()->create([
                'report_type' => $reportType->value,
                'format' => $reportType->format()->value,
                'title' => $this->reportTitle($reportType, $entity),
                'entity_type' => $entity === null ? null : $entity::class,
                'entity_id' => $entity?->getKey(),
                'city_id' => $this->resolveCityId($entity, $data),
                'object_id' => $this->resolveObjectId($entity, $data, $actor),
                'requested_by_user_id' => $actor->getKey(),
                'filters' => $this->filters($data),
                'status' => ReportStatus::Queued->value,
                'queued_at' => now(),
                'started_at' => null,
                'finished_at' => null,
                'error_message' => null,
            ]);

            if ($reportType->isPdf()) {
                GeneratePdfJob::dispatch($reportJob->getKey())->afterCommit();
            } else {
                ExportExcelJob::dispatch($reportJob->getKey())->afterCommit();
            }

            return $reportJob;
        });
    }

    public function generate(ReportJob $reportJob): void
    {
        $reportJob->refresh();
        $reportType = $reportJob->report_type instanceof ReportType
            ? $reportJob->report_type
            : ReportType::from((string) $reportJob->report_type);

        try {
            DB::transaction(function () use ($reportJob, $reportType): void {
                $reportJob->forceFill([
                    'status' => ReportStatus::Running->value,
                    'started_at' => now(),
                    'error_message' => null,
                ])->save();

                $artifact = $reportType->isPdf()
                    ? $this->buildPdfArtifact($reportJob, $reportType)
                    : $this->buildExcelArtifact($reportJob, $reportType);

                $this->storeFile(
                    reportJob: $reportJob,
                    content: $artifact['content'],
                    originalName: $artifact['original_name'],
                    mimeType: $artifact['mime_type'],
                    extension: $artifact['extension'],
                );

                $reportJob->forceFill([
                    'status' => ReportStatus::Completed->value,
                    'finished_at' => now(),
                ])->save();
            });

            $this->notificationService->notifyReportReady($reportJob->refresh()->loadMissing(['requestedBy', 'object']));
        } catch (Throwable $throwable) {
            $this->fail($reportJob, $throwable);
            throw $throwable;
        }
    }

    public function fail(ReportJob $reportJob, Throwable $throwable): void
    {
        $reportJob->forceFill([
            'status' => ReportStatus::Failed->value,
            'finished_at' => now(),
            'error_message' => $throwable->getMessage(),
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, scalar|bool>
     */
    private function filters(array $data): array
    {
        return array_filter([
            'object_id' => $data['object_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'search' => $data['search'] ?? null,
            'status' => $data['status'] ?? null,
            'date_from' => $data['date_from'] ?? null,
            'date_to' => $data['date_to'] ?? null,
            'include_defects' => (bool) ($data['include_defects'] ?? false),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveEntity(ReportType $reportType, array $data): ?Model
    {
        $entityClass = $reportType->entityClass();
        if ($entityClass === null) {
            return null;
        }

        $entityId = (int) ($data['entity_id'] ?? 0);
        if ($entityId <= 0) {
            throw new RuntimeException('Entity id is required for this report type.');
        }

        /** @var Model|null $entity */
        $entity = $entityClass::query()->find($entityId);

        if ($entity === null) {
            throw new RuntimeException('Related entity not found.');
        }

        if ($reportType === ReportType::LabShift && $entity instanceof Shift && $entity->type !== ShiftType::Lab) {
            throw new RuntimeException('The selected shift is not a lab shift.');
        }

        if ($reportType === ReportType::DecoderShift && $entity instanceof Shift && $entity->type !== ShiftType::Decoder) {
            throw new RuntimeException('The selected shift is not a decoder shift.');
        }

        return $entity;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveObjectId(?Model $entity, array $data, User $actor): ?int
    {
        if ($entity !== null) {
            return $this->objectIdFromEntity($entity);
        }

        if ($actor->hasRole('Администратор системы')) {
            return isset($data['object_id']) ? (int) $data['object_id'] : null;
        }

        return $actor->objectId();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveCityId(?Model $entity, array $data): ?int
    {
        if ($entity !== null) {
            return $this->cityIdFromEntity($entity);
        }

        return isset($data['city_id']) ? (int) $data['city_id'] : null;
    }

    private function reportTitle(ReportType $reportType, ?Model $entity): string
    {
        return $entity === null
            ? $reportType->label()
            : $reportType->label().' · #'.$entity->getKey();
    }

    /**
     * @return array{content: string, original_name: string, mime_type: string, extension: string}
     */
    private function buildExcelArtifact(ReportJob $reportJob, ReportType $reportType): array
    {
        $dataset = match ($reportType) {
            ReportType::Requests => $this->buildRequestsDataset($reportJob),
            ReportType::Welds => $this->buildWeldsDataset($reportJob),
            ReportType::Tasks => $this->buildTasksDataset($reportJob),
            ReportType::Results => $this->buildResultsDataset($reportJob, false),
            ReportType::ResultsDefects => $this->buildResultsDataset($reportJob, true),
            ReportType::Radiography => $this->buildRadiographyDataset($reportJob),
            ReportType::Equipment => $this->buildEquipmentDataset($reportJob),
            ReportType::DocumentsArchive => $this->buildDocumentsDataset($reportJob),
            default => throw new RuntimeException('Unsupported Excel report type.'),
        };

        $content = $this->excelWriter->build($dataset['sheet_name'], $dataset['headers'], $dataset['rows']);

        return [
            'content' => $content,
            'original_name' => Str::slug($reportJob->title, '-').'.xlsx',
            'mime_type' => ReportFormat::Excel->mimeType(),
            'extension' => 'xlsx',
        ];
    }

    /**
     * @return array{content: string, original_name: string, mime_type: string, extension: string}
     */
    private function buildPdfArtifact(ReportJob $reportJob, ReportType $reportType): array
    {
        $template = $reportType->template();
        if ($template === null) {
            throw new RuntimeException('Unsupported PDF report type.');
        }

        $rendered = match ($reportType) {
            ReportType::Conclusions => view($template, [
                'conclusion' => $this->loadEntity($reportJob, Conclusion::class, ['object.city', 'method', 'request', 'preparedBy', 'checkedBy', 'approvedBy', 'items.result.weld', 'versions.file']),
            ])->render(),
            ReportType::Registers => view($template, [
                'register' => $this->loadEntity($reportJob, TransferRegister::class, ['type', 'city', 'object.city', 'senderEmployee.object.city', 'receiverEmployee.object.city', 'items.related', 'items.file', 'acts.type', 'acts.files', 'archiveCases.items.related', 'archiveCases.items.file', 'archiveCases.register', 'files.uploadedBy']),
            ])->render(),
            ReportType::Acts => view($template, [
                'act' => $this->loadEntity($reportJob, Act::class, ['type', 'register.type', 'city', 'object.city', 'files.uploadedBy']),
            ])->render(),
            ReportType::LabShift => view($template, [
                'shift' => $this->loadEntity($reportJob, Shift::class, ['object.city', 'employee.object.city', 'labReport', 'labRegulatoryWorks', 'filmTransactions', 'chemicalTransactions', 'chemicalRequests']),
                'reportTitle' => $reportJob->title,
            ])->render(),
            ReportType::DecoderShift => view($template, [
                'shift' => $this->loadEntity($reportJob, Shift::class, ['object.city', 'employee.object.city', 'decoderReport', 'decoderFilmGroups', 'decoderRejects', 'decoderForgerySuspicion', 'decoderCleanups', 'decoderDecryptions']),
                'reportTitle' => $reportJob->title,
            ])->render(),
            default => throw new RuntimeException('Unsupported PDF report type.'),
        };

        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R/u', trim($rendered)) ?: [],
        ), static fn (string $line): bool => $line !== ''));

        $content = $this->pdfWriter->build($lines);

        return [
            'content' => $content,
            'original_name' => Str::slug($reportJob->title, '-').'.pdf',
            'mime_type' => ReportFormat::Pdf->mimeType(),
            'extension' => 'pdf',
        ];
    }

    /**
     * @return array{sheet_name: string, headers: list<string>, rows: list<list<string|null>>}
     */
    private function buildRequestsDataset(ReportJob $reportJob): array
    {
        $query = NdtRequest::query()->with(['object.city', 'organization'])->withCount('welds');
        $this->applyObjectScope($query, $reportJob);

        $search = $this->filter($reportJob, 'search');
        if ($search !== null) {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('request_number', 'like', '%'.$search.'%')
                    ->orWhere('basis', 'like', '%'.$search.'%')
                    ->orWhere('comment', 'like', '%'.$search.'%')
                    ->orWhereHas('organization', fn (Builder $organization) => $organization->where('name', 'like', '%'.$search.'%'));
            });
        }

        $status = $this->filter($reportJob, 'status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $dateFrom = $this->filter($reportJob, 'date_from');
        if ($dateFrom !== null) {
            $query->whereDate('request_date', '>=', $dateFrom);
        }

        $dateTo = $this->filter($reportJob, 'date_to');
        if ($dateTo !== null) {
            $query->whereDate('request_date', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->orderByDesc('request_date')->orderByDesc('id')->limit(1000)->get() as $request) {
            $rows[] = [
                $request->request_number,
                $request->request_date?->format('d.m.Y'),
                $request->object?->city?->name,
                $request->object?->name,
                $request->organization?->name,
                $request->status->label(),
                $request->due_date?->format('d.m.Y'),
                (string) $request->welds_count,
            ];
        }

        return [
            'sheet_name' => ReportType::Requests->sheetName(),
            'headers' => ['Номер', 'Дата', 'Город', 'Объект/участок', 'Заказчик', 'Статус', 'Срок', 'Стыков'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{sheet_name: string, headers: list<string>, rows: list<list<string|null>>}
     */
    private function buildWeldsDataset(ReportJob $reportJob): array
    {
        $query = Weld::query()->with(['object.city', 'drawing', 'line', 'ndtMethods']);
        $this->applyObjectScope($query, $reportJob);

        $search = $this->filter($reportJob, 'search');
        if ($search !== null) {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('weld_number', 'like', '%'.$search.'%')
                    ->orWhere('comment', 'like', '%'.$search.'%')
                    ->orWhereHas('drawing', fn (Builder $drawing) => $drawing->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('line', fn (Builder $line) => $line->where('name', 'like', '%'.$search.'%'));
            });
        }

        $status = $this->filter($reportJob, 'status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $dateFrom = $this->filter($reportJob, 'date_from');
        if ($dateFrom !== null) {
            $query->whereDate('welded_at', '>=', $dateFrom);
        }

        $dateTo = $this->filter($reportJob, 'date_to');
        if ($dateTo !== null) {
            $query->whereDate('welded_at', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->orderByDesc('id')->limit(1000)->get() as $weld) {
            $rows[] = [
                $weld->weld_number,
                $weld->object?->city?->name,
                $weld->object?->name,
                $weld->drawing?->name,
                $weld->line?->name,
                $weld->status->label(),
                $weld->ndtMethods->map(fn ($method) => $method->name)->implode(', '),
            ];
        }

        return [
            'sheet_name' => ReportType::Welds->sheetName(),
            'headers' => ['Стык', 'Город', 'Объект/участок', 'Чертеж', 'Линия', 'Статус', 'Методы'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{sheet_name: string, headers: list<string>, rows: list<list<string|null>>}
     */
    private function buildTasksDataset(ReportJob $reportJob): array
    {
        $query = NdtTask::query()->with(['method', 'request.object.city', 'object', 'assigneeEmployee']);
        $this->applyObjectScope($query, $reportJob);

        $search = $this->filter($reportJob, 'search');
        if ($search !== null) {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('task_number', 'like', '%'.$search.'%')
                    ->orWhere('comment', 'like', '%'.$search.'%');
            });
        }

        $status = $this->filter($reportJob, 'status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $dateFrom = $this->filter($reportJob, 'date_from');
        if ($dateFrom !== null) {
            $query->whereDate('planned_date', '>=', $dateFrom);
        }

        $dateTo = $this->filter($reportJob, 'date_to');
        if ($dateTo !== null) {
            $query->whereDate('planned_date', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->orderByDesc('planned_date')->orderByDesc('id')->limit(1000)->get() as $task) {
            $rows[] = [
                $task->task_number,
                $task->object?->city?->name ?? $task->request?->object?->city?->name,
                $task->object?->name ?? $task->request?->object?->name,
                $task->request?->request_number,
                $task->assigneeEmployee?->fullName(),
                $task->method?->name,
                $task->status->label(),
                $task->planned_date?->format('d.m.Y'),
            ];
        }

        return [
            'sheet_name' => ReportType::Tasks->sheetName(),
            'headers' => ['Задание', 'Город', 'Объект/участок', 'Заявка', 'Исполнитель', 'Метод', 'Статус', 'Срок'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{sheet_name: string, headers: list<string>, rows: list<list<string|null>>}
     */
    private function buildResultsDataset(ReportJob $reportJob, bool $onlyDefects): array
    {
        $query = NdtResult::query()->with(['weld.object.city', 'method', 'executorEmployee', 'task', 'defects']);
        $this->applyObjectScope($query, $reportJob, 'weld');

        $search = $this->filter($reportJob, 'search');
        if ($search !== null) {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('result_text', 'like', '%'.$search.'%')
                    ->orWhere('comment', 'like', '%'.$search.'%')
                    ->orWhereHas('weld', fn (Builder $weld) => $weld->where('weld_number', 'like', '%'.$search.'%'))
                    ->orWhereHas('method', fn (Builder $method) => $method->where('name', 'like', '%'.$search.'%'));
            });
        }

        $status = $this->filter($reportJob, 'status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $dateFrom = $this->filter($reportJob, 'date_from');
        if ($dateFrom !== null) {
            $query->whereDate('control_date', '>=', $dateFrom);
        }

        $dateTo = $this->filter($reportJob, 'date_to');
        if ($dateTo !== null) {
            $query->whereDate('control_date', '<=', $dateTo);
        }

        if ($onlyDefects || (bool) ($reportJob->filters['include_defects'] ?? false)) {
            $query->where(function (Builder $nested): void {
                $nested->where('status', NdtResultStatus::Defect->value)
                    ->orWhereHas('defects');
            });
        }

        $rows = [];
        foreach ($query->orderByDesc('control_date')->orderByDesc('id')->limit(1000)->get() as $result) {
            $rows[] = [
                (string) $result->getKey(),
                $result->weld?->object?->city?->name,
                $result->weld?->object?->name,
                $result->weld?->weld_number,
                $result->method?->name,
                $result->executorEmployee?->fullName(),
                $result->status->label(),
                $result->result_text,
            ];
        }

        return [
            'sheet_name' => $onlyDefects ? 'Результаты с дефектами' : ReportType::Results->sheetName(),
            'headers' => ['Результат', 'Город', 'Объект/участок', 'Стык', 'Метод', 'Исполнитель', 'Статус', 'Результат'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{sheet_name: string, headers: list<string>, rows: list<list<string|null>>}
     */
    private function buildRadiographyDataset(ReportJob $reportJob): array
    {
        $query = RtResult::query()->with(['ndtResult.weld.object.city', 'films', 'reshoots', 'densityMeasurements']);
        $this->applyObjectScope($query, $reportJob, 'ndtResult.weld');

        $search = $this->filter($reportJob, 'search');
        if ($search !== null) {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('barcode', 'like', '%'.$search.'%')
                    ->orWhere('conclusion_number', 'like', '%'.$search.'%')
                    ->orWhere('result_text', 'like', '%'.$search.'%');
            });
        }

        $status = $this->filter($reportJob, 'status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $dateFrom = $this->filter($reportJob, 'date_from');
        if ($dateFrom !== null) {
            $query->whereDate('control_date', '>=', $dateFrom);
        }

        $dateTo = $this->filter($reportJob, 'date_to');
        if ($dateTo !== null) {
            $query->whereDate('control_date', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->orderByDesc('control_date')->orderByDesc('id')->limit(1000)->get() as $rtResult) {
            $rows[] = [
                (string) $rtResult->getKey(),
                $rtResult->ndtResult?->weld?->object?->city?->name,
                $rtResult->ndtResult?->weld?->object?->name,
                $rtResult->ndtResult?->weld?->weld_number,
                $rtResult->barcode,
                $rtResult->status->label(),
                (string) $rtResult->films->count(),
                (string) $rtResult->reshoots->count(),
                (string) $rtResult->densityMeasurements->count(),
            ];
        }

        return [
            'sheet_name' => ReportType::Radiography->sheetName(),
            'headers' => ['РК-результат', 'Город', 'Объект/участок', 'Стык', 'Barcode', 'Статус', 'Снимков', 'Пересветов', 'Плотностей'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{sheet_name: string, headers: list<string>, rows: list<list<string|null>>}
     */
    private function buildEquipmentDataset(ReportJob $reportJob): array
    {
        $query = Equipment::query()->with(['object.city', 'type', 'latestVerification', 'latestCalibration']);
        $this->applyObjectScope($query, $reportJob);

        $search = $this->filter($reportJob, 'search');
        if ($search !== null) {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('name', 'like', '%'.$search.'%')
                    ->orWhere('inventory_number', 'like', '%'.$search.'%')
                    ->orWhere('serial_number', 'like', '%'.$search.'%');
            });
        }

        $status = $this->filter($reportJob, 'status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $rows = [];
        foreach ($query->orderBy('name')->limit(1000)->get() as $equipment) {
            $rows[] = [
                $equipment->name,
                $equipment->object?->city?->name,
                $equipment->object?->name,
                $equipment->type?->name,
                $equipment->inventory_number,
                $equipment->status->label(),
                $equipment->latestVerification?->verified_at?->format('d.m.Y'),
                $equipment->latestCalibration?->calibrated_at?->format('d.m.Y'),
            ];
        }

        return [
            'sheet_name' => ReportType::Equipment->sheetName(),
            'headers' => ['Оборудование', 'Город', 'Объект/участок', 'Тип', 'Инвентарный номер', 'Статус', 'Поверка', 'Калибровка'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{sheet_name: string, headers: list<string>, rows: list<list<string|null>>}
     */
    private function buildDocumentsDataset(ReportJob $reportJob): array
    {
        $query = Document::query()->with(['type', 'object.city', 'equipment', 'files']);
        $this->applyObjectScope($query, $reportJob);

        $search = $this->filter($reportJob, 'search');
        if ($search !== null) {
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('number', 'like', '%'.$search.'%')
                    ->orWhere('comment', 'like', '%'.$search.'%')
                    ->orWhereHas('type', fn (Builder $type) => $type->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('equipment', fn (Builder $equipment) => $equipment->where('name', 'like', '%'.$search.'%'));
            });
        }

        $status = $this->filter($reportJob, 'status');
        if ($status !== null) {
            $query->where('status', $status);
        }

        $dateFrom = $this->filter($reportJob, 'date_from');
        if ($dateFrom !== null) {
            $query->whereDate('document_date', '>=', $dateFrom);
        }

        $dateTo = $this->filter($reportJob, 'date_to');
        if ($dateTo !== null) {
            $query->whereDate('document_date', '<=', $dateTo);
        }

        $rows = [];
        foreach ($query->orderByDesc('document_date')->orderByDesc('id')->limit(1000)->get() as $document) {
            $rows[] = [
                $document->number,
                $document->document_date?->format('d.m.Y'),
                $document->object?->city?->name,
                $document->object?->name,
                $document->type?->name,
                $document->equipment?->name,
                $document->status->label(),
                (string) $document->files->count(),
            ];
        }

        return [
            'sheet_name' => ReportType::DocumentsArchive->sheetName(),
            'headers' => ['Документ', 'Дата', 'Город', 'Объект/участок', 'Тип', 'Оборудование', 'Статус', 'Файлов'],
            'rows' => $rows,
        ];
    }

    private function applyObjectScope(Builder $query, ReportJob $reportJob, string $relationPath = 'object'): void
    {
        if ($reportJob->object_id !== null) {
            if (str_contains($relationPath, '.')) {
                $query->whereHas($relationPath, fn (Builder $nested) => $nested->where('object_id', $reportJob->object_id));
            } else {
                $query->where($relationPath, $reportJob->object_id);
            }
        }

        if ($reportJob->city_id !== null) {
            if ($relationPath === 'object') {
                $query->whereHas('object', fn (Builder $nested) => $nested->where('city_id', $reportJob->city_id));
            } elseif ($relationPath === 'weld') {
                $query->whereHas('weld.object', fn (Builder $nested) => $nested->where('city_id', $reportJob->city_id));
            } elseif ($relationPath === 'ndtResult.weld') {
                $query->whereHas('ndtResult.weld.object', fn (Builder $nested) => $nested->where('city_id', $reportJob->city_id));
            } elseif ($relationPath === 'object_id') {
                $query->whereHas('object', fn (Builder $nested) => $nested->where('city_id', $reportJob->city_id));
            }
        }
    }

    private function filter(ReportJob $reportJob, string $key): ?string
    {
        $value = $reportJob->filters[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function objectIdFromEntity(Model $entity): ?int
    {
        return match (true) {
            $entity instanceof Conclusion => $entity->object_id,
            $entity instanceof TransferRegister => $entity->object_id,
            $entity instanceof Act => $entity->object_id,
            $entity instanceof Shift => $entity->object_id,
            default => null,
        };
    }

    private function cityIdFromEntity(Model $entity): ?int
    {
        return match (true) {
            $entity instanceof Conclusion => $entity->object?->city_id,
            $entity instanceof TransferRegister => $entity->object?->city_id,
            $entity instanceof Act => $entity->object?->city_id,
            $entity instanceof Shift => $entity->object?->city_id,
            default => null,
        };
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $class
     * @param  list<string>  $relations
     * @return TModel
     */
    private function loadEntity(ReportJob $reportJob, string $class, array $relations): Model
    {
        /** @var Model|null $entity */
        $entity = $class::query()->with($relations)->find($reportJob->entity_id);

        if ($entity === null) {
            throw new RuntimeException('Related entity not found.');
        }

        return $entity;
    }

    private function storeFile(ReportJob $reportJob, string $content, string $originalName, string $mimeType, string $extension): File
    {
        $disk = config('filesystems.default', 'private');
        $storageName = (string) Str::uuid().'.'.$extension;
        $storagePath = 'reports/'.now()->format('Y/m/d').'/'.$storageName;
        $hash = hash('sha256', $content);

        if (Storage::disk($disk)->put($storagePath, $content) !== true) {
            throw new RuntimeException('Unable to store report file.');
        }

        return File::query()->create([
            'original_name' => $originalName,
            'storage_name' => $storageName,
            'storage_path' => $storagePath,
            'disk' => $disk,
            'mime_type' => $mimeType,
            'size' => strlen($content),
            'hash' => $hash,
            'uploaded_by_user_id' => $reportJob->requested_by_user_id,
            'related_type' => $reportJob::class,
            'related_id' => $reportJob->getKey(),
            'status' => FileStatus::Active->value,
        ]);
    }
}
