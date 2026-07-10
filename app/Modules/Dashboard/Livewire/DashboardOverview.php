<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Livewire;

use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class DashboardOverview extends Component
{
    public function render(): View
    {
        return view('modules.dashboard.livewire.dashboard-overview', [
            'summary' => app(DashboardService::class)->overview(auth()->user()),
        ]);
    }
}
