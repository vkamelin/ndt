<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Access\Seeders\RolePermissionSeeder;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(RegisterDictionarySeeder::class);
        $this->call(NdtMethodSeeder::class);
        $this->call(NotificationTemplateSeeder::class);
        $this->call(ReferenceDataSeeder::class);
        $this->call(DemoUserSeeder::class);
    }
}
