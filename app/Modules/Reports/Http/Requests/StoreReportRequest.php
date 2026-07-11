<?php

declare(strict_types=1);

namespace App\Modules\Reports\Http\Requests;

use App\Modules\Reports\Enums\ReportType;
use App\Modules\Shifts\Models\Shift;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null || ! $user->can('reports.manage')) {
            return false;
        }

        $reportType = ReportType::tryFrom($this->string('report_type')->toString());

        if ($reportType === null) {
            return false;
        }

        if (! $reportType->isEntityReport()) {
            $objectId = $this->filled('object_id') ? (int) $this->input('object_id') : $user->objectId();

            if (! $user->hasRole('Администратор системы') && ($objectId === null || $user->objectId() !== $objectId)) {
                return false;
            }

            return true;
        }

        $entityId = (int) $this->input('entity_id');
        if ($entityId <= 0) {
            return false;
        }

        $entityClass = $reportType->entityClass();
        if ($entityClass === null || ! class_exists($entityClass)) {
            return false;
        }

        /** @var Model|null $entity */
        $entity = $entityClass::query()->find($entityId);

        if ($entity === null) {
            return false;
        }

        if ($reportType === ReportType::LabShift && $entity instanceof Shift && $entity->type->value !== 'lab') {
            return false;
        }

        if ($reportType === ReportType::DecoderShift && $entity instanceof Shift && $entity->type->value !== 'decoder') {
            return false;
        }

        return Gate::forUser($user)->allows('view', $entity) || Gate::forUser($user)->allows('manage', $entity);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'report_type' => ['required', Rule::enum(ReportType::class)],
            'object_id' => ['nullable', 'integer', 'exists:objects,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'entity_type' => ['nullable', 'string', 'max:255'],
            'entity_id' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'include_defects' => ['nullable', 'boolean'],
        ];
    }
}
