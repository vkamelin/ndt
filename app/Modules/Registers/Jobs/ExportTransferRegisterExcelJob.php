<?php

declare(strict_types=1);

namespace App\Modules\Registers\Jobs;

use App\Models\User;
use App\Modules\Documents\Enums\FileStatus;
use App\Modules\Documents\Models\File;
use App\Modules\Registers\Models\TransferRegister;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

final class ExportTransferRegisterExcelJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $transferRegisterId,
        public readonly ?int $actorId = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {
    }

    public function handle(): void
    {
        $register = TransferRegister::query()->with(['type', 'city', 'object.city', 'senderEmployee', 'receiverEmployee', 'items.related'])->findOrFail($this->transferRegisterId);
        $actor = $this->actorId === null ? null : User::query()->find($this->actorId);
        $rows = [];

        foreach ($register->items as $item) {
            $rows[] = [
                'register_number' => $register->number,
                'register_date' => $register->date?->toDateString(),
                'register_type' => $register->type?->name,
                'object' => $register->object?->name,
                'status' => $register->status->label(),
                'item_sort_order' => $item->sort_order,
                'item_type' => class_basename($item->related_type),
                'item_id' => (string) $item->related_id,
                'comment' => $item->comment,
            ];
        }

        if ($rows === []) {
            $rows[] = [
                'register_number' => $register->number,
                'register_date' => $register->date?->toDateString(),
                'register_type' => $register->type?->name,
                'object' => $register->object?->name,
                'status' => $register->status->label(),
                'item_sort_order' => '',
                'item_type' => '',
                'item_id' => '',
                'comment' => '',
            ];
        }

        $content = $this->buildXlsx($rows);
        $disk = config('filesystems.default', 'private');
        $storageName = (string) Str::uuid().'.xlsx';
        $storagePath = 'registers/'.now()->format('Y/m/d').'/'.$storageName;

        Storage::disk($disk)->put($storagePath, $content);

        File::query()->create([
            'original_name' => $register->number.'.xlsx',
            'storage_name' => $storageName,
            'storage_path' => $storagePath,
            'disk' => $disk,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'size' => strlen($content),
            'hash' => hash('sha256', $content),
            'uploaded_by_user_id' => $actor?->getKey(),
            'related_type' => $register::class,
            'related_id' => $register->getKey(),
            'status' => FileStatus::Active->value,
        ]);
    }

    /**
     * @param  array<int, array<string, string|int|null>>  $rows
     */
    private function buildXlsx(array $rows): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'register_xlsx_');
        if ($tmpPath === false) {
            throw new \RuntimeException('Unable to create temporary XLSX file.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create XLSX archive.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->relsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($rows));
        $zip->close();

        $content = file_get_contents($tmpPath);
        unlink($tmpPath);

        if ($content === false) {
            throw new \RuntimeException('Unable to read XLSX archive.');
        }

        return $content;
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML;
    }

    private function relsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    private function workbookXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Register" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    private function workbookRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML;
    }

    /**
     * @param  array<int, array<string, string|int|null>>  $rows
     */
    private function sheetXml(array $rows): string
    {
        $headers = array_keys($rows[0]);
        $xmlRows = [];
        $xmlRows[] = $this->xmlRow(1, $headers, true);

        $rowNumber = 2;
        foreach ($rows as $row) {
            $xmlRows[] = $this->xmlRow($rowNumber, array_map(static fn (mixed $value): string => (string) ($value ?? ''), array_values($row)));
            $rowNumber++;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'
            .implode('', $xmlRows)
            .'</sheetData>'
            .'</worksheet>';
    }

    /**
     * @param  array<int, string>  $values
     */
    private function xmlRow(int $rowNumber, array $values, bool $bold = false): string
    {
        $cells = [];
        foreach ($values as $index => $value) {
            $cellRef = $this->columnLetter($index + 1).$rowNumber;
            $cells[] = '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8').'</t></is></c>';
        }

        return '<row r="'.$rowNumber.'">'.implode('', $cells).'</row>';
    }

    private function columnLetter(int $number): string
    {
        $letter = '';

        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $letter = chr(65 + $remainder).$letter;
            $number = intdiv($number - 1, 26);
        }

        return $letter;
    }
}
