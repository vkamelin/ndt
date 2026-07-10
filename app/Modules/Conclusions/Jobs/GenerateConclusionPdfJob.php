<?php

declare(strict_types=1);

namespace App\Modules\Conclusions\Jobs;

use App\Models\User;
use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Conclusions\Services\ConclusionVersionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GenerateConclusionPdfJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $conclusionId,
        public readonly string $basis,
        public readonly ?int $actorId = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {
    }

    public function handle(ConclusionVersionService $versionService): void
    {
        $conclusion = Conclusion::query()->findOrFail($this->conclusionId);
        $actor = $this->actorId === null ? null : User::query()->find($this->actorId);

        $versionService->createVersion(
            conclusion: $conclusion,
            basis: $this->basis,
            actor: $actor,
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent,
        );
    }
}
