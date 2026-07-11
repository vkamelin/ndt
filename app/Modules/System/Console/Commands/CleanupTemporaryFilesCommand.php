<?php

declare(strict_types=1);

namespace App\Modules\System\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

final class CleanupTemporaryFilesCommand extends Command
{
    protected $signature = 'system:cleanup-temporary-files
        {--path= : Temporary directory to scan}
        {--hours=24 : Remove files older than this many hours}';

    protected $description = 'Remove stale temporary XLSX files created by export jobs.';

    public function handle(): int
    {
        $directory = rtrim((string) ($this->option('path') ?: sys_get_temp_dir()), DIRECTORY_SEPARATOR);
        $cutoff = now()->subHours((int) $this->option('hours'))->getTimestamp();
        $deleted = 0;

        if (! is_dir($directory)) {
            $this->warn('Temporary directory was not found: '.$directory);

            return self::SUCCESS;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (! $this->matchesPrefix($file->getFilename())) {
                continue;
            }

            if ($file->getMTime() >= $cutoff) {
                continue;
            }

            try {
                if (@unlink($file->getPathname())) {
                    $deleted++;
                }
            } catch (Throwable) {
                continue;
            }
        }

        $this->info(sprintf('Removed %d stale temporary file(s).', $deleted));

        return self::SUCCESS;
    }

    private function matchesPrefix(string $filename): bool
    {
        return Str::startsWith($filename, config('system.cleanup.temporary_file_prefixes', []));
    }
}
