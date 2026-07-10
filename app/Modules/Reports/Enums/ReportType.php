<?php

declare(strict_types=1);

namespace App\Modules\Reports\Enums;

use App\Modules\Conclusions\Models\Conclusion;
use App\Modules\Documents\Models\Document;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\Registers\Models\Act;
use App\Modules\Registers\Models\TransferRegister;
use App\Modules\Shifts\Models\Shift;

enum ReportType: string
{
    case Requests = 'requests';
    case Welds = 'welds';
    case Tasks = 'tasks';
    case Results = 'results';
    case ResultsDefects = 'results_defects';
    case Radiography = 'radiography';
    case Conclusions = 'conclusions';
    case Registers = 'registers';
    case Acts = 'acts';
    case LabShift = 'lab_shift';
    case DecoderShift = 'decoder_shift';
    case Equipment = 'equipment';
    case DocumentsArchive = 'documents_archive';

    public function label(): string
    {
        return match ($this) {
            self::Requests => 'Заявки',
            self::Welds => 'Стыки',
            self::Tasks => 'Задания',
            self::Results => 'Результаты',
            self::ResultsDefects => 'Результаты с дефектами',
            self::Radiography => 'РК',
            self::Conclusions => 'Заключения',
            self::Registers => 'Реестры',
            self::Acts => 'Акты',
            self::LabShift => 'Смена лаборанта',
            self::DecoderShift => 'Смена дешифровщика',
            self::Equipment => 'Оборудование',
            self::DocumentsArchive => 'Документы и архив',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Requests => 'Сводка по заявкам с фильтрами по объекту, статусу и датам.',
            self::Welds => 'Список стыков с привязкой к чертежам и линиям.',
            self::Tasks => 'Список заданий исполнителям.',
            self::Results => 'Общий список результатов контроля.',
            self::ResultsDefects => 'Результаты, где зафиксированы дефекты.',
            self::Radiography => 'Сводка по радиографическому контролю.',
            self::Conclusions => 'Печатная форма заключения.',
            self::Registers => 'Печатная форма реестра передачи.',
            self::Acts => 'Печатная форма акта.',
            self::LabShift => 'Печатная форма сменного отчета лаборанта.',
            self::DecoderShift => 'Печатная форма сменного отчета дешифровщика.',
            self::Equipment => 'Список оборудования и его контрольных сроков.',
            self::DocumentsArchive => 'Сводка по документам, файлам и архивным связкам.',
        };
    }

    public function format(): ReportFormat
    {
        return match ($this) {
            self::Conclusions,
            self::Registers,
            self::Acts,
            self::LabShift,
            self::DecoderShift => ReportFormat::Pdf,
            default => ReportFormat::Excel,
        };
    }

    public function template(): ?string
    {
        return match ($this) {
            self::Conclusions => 'pdf.reports.conclusion',
            self::Registers => 'pdf.reports.register',
            self::Acts => 'pdf.reports.act',
            self::LabShift => 'pdf.reports.lab-shift',
            self::DecoderShift => 'pdf.reports.decoder-shift',
            default => null,
        };
    }

    public function fileExtension(): string
    {
        return $this->format()->extension();
    }

    public function mimeType(): string
    {
        return $this->format()->mimeType();
    }

    public function sheetName(): string
    {
        return match ($this) {
            self::Requests => 'Заявки',
            self::Welds => 'Стыки',
            self::Tasks => 'Задания',
            self::Results => 'Результаты',
            self::ResultsDefects => 'Результаты с дефектами',
            self::Radiography => 'РК',
            self::Equipment => 'Оборудование',
            self::DocumentsArchive => 'Документы и архив',
            default => $this->label(),
        };
    }

    public function entityClass(): ?string
    {
        return match ($this) {
            self::Conclusions => Conclusion::class,
            self::Registers => TransferRegister::class,
            self::Acts => Act::class,
            self::LabShift, self::DecoderShift => Shift::class,
            default => null,
        };
    }

    public function isEntityReport(): bool
    {
        return $this->entityClass() !== null;
    }

    public function isPdf(): bool
    {
        return $this->format() === ReportFormat::Pdf;
    }

    public function isExcel(): bool
    {
        return $this->format() === ReportFormat::Excel;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
