<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\NdtTasks\Enums\NdtMethodCode;
use App\Modules\NdtTasks\Models\NdtMethod;
use Illuminate\Database\Seeder;

final class NdtMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach (NdtMethodCode::cases() as $code) {
            NdtMethod::query()->updateOrCreate(
                ['code' => $code->value],
                [
                    'name' => $code->fullName(),
                    'is_active' => true,
                    'comment' => null,
                ],
            );
        }
    }
}
