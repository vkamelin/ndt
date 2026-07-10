<?php

declare(strict_types=1);

namespace App\Modules\Reports\Enums;

enum ReportFormat: string
{
    case Excel = 'excel';
    case Pdf = 'pdf';

    public function label(): string
    {
        return match ($this) {
            self::Excel => 'Excel',
            self::Pdf => 'PDF',
        };
    }

    public function extension(): string
    {
        return match ($this) {
            self::Excel => 'xlsx',
            self::Pdf => 'pdf',
        };
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::Excel => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::Pdf => 'application/pdf',
        };
    }
}
