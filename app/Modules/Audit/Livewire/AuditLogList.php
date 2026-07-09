<?php

declare(strict_types=1);

namespace App\Modules\Audit\Livewire;

use App\Models\User;
use App\Modules\Audit\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

final class AuditLogList extends Component
{
    use WithPagination;

    /**
     * @var array<string, array{except: string}>
     */
    protected array $queryString = [
        'entityType' => ['except' => ''],
        'actorUserId' => ['except' => ''],
        'operation' => ['except' => ''],
        'fromDate' => ['except' => ''],
        'toDate' => ['except' => ''],
    ];

    public string $entityType = '';

    public string $actorUserId = '';

    public string $operation = '';

    public string $fromDate = '';

    public string $toDate = '';

    public function updatedEntityType(): void
    {
        $this->resetPage();
    }

    public function updatedActorUserId(): void
    {
        $this->resetPage();
    }

    public function updatedOperation(): void
    {
        $this->resetPage();
    }

    public function updatedFromDate(): void
    {
        $this->resetPage();
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $logs = $this->queryLogs()->paginate(15);

        return view('modules.audit.livewire.audit-log-list', [
            'logs' => $logs,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'entityTypes' => $this->entityTypes(),
            'operations' => $this->operations(),
        ]);
    }

    /**
     * @return list<string>
     */
    private function entityTypes(): array
    {
        return AuditLog::query()
            ->select('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->all();
    }

    /**
     * @return list<string>
     */
    private function operations(): array
    {
        return AuditLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->all();
    }

    private function queryLogs(): Builder
    {
        return AuditLog::query()
            ->with('actor')
            ->when($this->entityType !== '', function (Builder $query): void {
                $query->where('subject_type', $this->entityType);
            })
            ->when($this->actorUserId !== '', function (Builder $query): void {
                $query->where('actor_user_id', (int) $this->actorUserId);
            })
            ->when($this->operation !== '', function (Builder $query): void {
                $query->where('event', $this->operation);
            })
            ->when($this->fromDate !== '', function (Builder $query): void {
                $query->whereDate('created_at', '>=', $this->fromDate);
            })
            ->when($this->toDate !== '', function (Builder $query): void {
                $query->whereDate('created_at', '<=', $this->toDate);
            })
            ->orderByDesc('created_at');
    }
}
