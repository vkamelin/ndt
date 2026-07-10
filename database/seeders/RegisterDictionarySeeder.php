<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Admin\Models\ActType;
use App\Modules\Admin\Models\RegisterType;
use Illuminate\Database\Seeder;

final class RegisterDictionarySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Передача пленок',
            'Передача заключений',
            'Передача в технический надзор',
            'Передача в ПТО',
            'Передача в архив',
            'Внутренний реестр лаборатории',
        ] as $name) {
            RegisterType::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true, 'comment' => null],
            );
        }

        foreach ([
            'Акт ВР',
        ] as $name) {
            ActType::query()->updateOrCreate(
                ['name' => $name],
                ['is_active' => true, 'comment' => null],
            );
        }
    }
}
