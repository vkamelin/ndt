<?php

declare(strict_types=1);

namespace Tests\Unit\System;

use Tests\TestCase;

final class ProductionArtifactsTest extends TestCase
{
    public function test_production_artifacts_cover_backup_scheduler_and_supervisor_requirements(): void
    {
        $this->assertFileIsReadable(base_path('scripts/backup/mysql-backup.sh'));
        $this->assertFileIsReadable(base_path('scripts/backup/files-backup.sh'));
        $this->assertFileIsReadable(base_path('scripts/backup/verify-backup.sh'));
        $this->assertFileIsReadable(base_path('deploy/cron/laravel-scheduler.cron'));
        $this->assertFileIsReadable(base_path('docker/supervisor/conf.d/queue-worker.conf'));

        $this->assertStringContainsString('mysqldump', $this->readFile('scripts/backup/mysql-backup.sh'));
        $this->assertStringContainsString('sha256sum', $this->readFile('scripts/backup/mysql-backup.sh'));
        $this->assertStringContainsString('tar -czf', $this->readFile('scripts/backup/files-backup.sh'));
        $this->assertStringContainsString('BACKUP_STORAGE_PATH', $this->readFile('scripts/backup/files-backup.sh'));
        $this->assertStringContainsString('gzip -t', $this->readFile('scripts/backup/verify-backup.sh'));
        $this->assertStringContainsString('sha256sum -c', $this->readFile('scripts/backup/verify-backup.sh'));
        $this->assertStringContainsString('php artisan schedule:run', $this->readFile('deploy/cron/laravel-scheduler.cron'));
        $this->assertStringContainsString('storage/logs/scheduler.log', $this->readFile('deploy/cron/laravel-scheduler.cron'));
        $this->assertStringContainsString('queue:work redis', $this->readFile('docker/supervisor/conf.d/queue-worker.conf'));
        $this->assertStringContainsString('storage/logs/queue-worker.log', $this->readFile('docker/supervisor/conf.d/queue-worker.conf'));
    }

    public function test_backup_scripts_are_marked_executable(): void
    {
        $this->assertTrue(is_executable(base_path('scripts/backup/mysql-backup.sh')));
        $this->assertTrue(is_executable(base_path('scripts/backup/files-backup.sh')));
        $this->assertTrue(is_executable(base_path('scripts/backup/verify-backup.sh')));
    }

    public function test_readme_points_to_existing_project_documentation(): void
    {
        $readme = $this->readFile('README.md');

        $this->assertStringContainsString('docs/01-architecture.md', $readme);
        $this->assertStringContainsString('docs/09-testing-strategy.md', $readme);
        $this->assertStringContainsString('docs/operations/README.md', $readme);
        $this->assertStringNotContainsString('docs/testing.md', $readme);
        $this->assertStringNotContainsString('docs/deployment.md', $readme);
    }

    private function readFile(string $relativePath): string
    {
        $contents = file_get_contents(base_path($relativePath));

        $this->assertNotFalse($contents, sprintf('Failed to read "%s".', $relativePath));

        return $contents;
    }
}
