<?php

declare(strict_types=1);

namespace App\Modules\Reports\Jobs;

use App\Modules\Reports\Models\ReportJob;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ExportExcelJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $reportJobId,
    ) {
    }

    public function handle(ReportService $service): void
    {
        $service->generate(ReportJob::query()->findOrFail($this->reportJobId));
    }
}
