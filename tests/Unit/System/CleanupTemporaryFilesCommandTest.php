<?php

declare(strict_types=1);

namespace Tests\Unit\System;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class CleanupTemporaryFilesCommandTest extends TestCase
{
    public function test_cleanup_command_removes_only_stale_export_files(): void
    {
        $directory = sys_get_temp_dir().'/ndt-cleanup-'.uniqid('', true);
        mkdir($directory, 0777, true);

        $staleReport = $directory.'/report_xlsx_stale.tmp';
        $freshReport = $directory.'/report_xlsx_fresh.tmp';
        $otherFile = $directory.'/other_stale.tmp';

        file_put_contents($staleReport, 'old');
        file_put_contents($freshReport, 'new');
        file_put_contents($otherFile, 'other');

        touch($staleReport, now()->subHours(48)->getTimestamp());
        touch($freshReport, now()->getTimestamp());
        touch($otherFile, now()->subHours(48)->getTimestamp());

        Artisan::call('system:cleanup-temporary-files', [
            '--path' => $directory,
            '--hours' => 24,
        ]);

        $this->assertFileDoesNotExist($staleReport);
        $this->assertFileExists($freshReport);
        $this->assertFileExists($otherFile);

        @unlink($freshReport);
        @unlink($otherFile);
        @rmdir($directory);
    }
}
