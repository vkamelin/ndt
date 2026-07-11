<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Services;

use App\Modules\NdtRequests\DTO\NdtRequestWeldData;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use ZipArchive;

final class NdtRequestImportService
{
    /**
     * @return array<int, string>
     */
    public function headers(): array
    {
        return [
            'Номер стыка',
            'Диаметр',
            'Толщина',
            'Дата сварки',
            'PWHT',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function sampleRow(): array
    {
        return [
            'Номер стыка' => '1-01',
            'Диаметр' => '325',
            'Толщина' => '12',
            'Дата сварки' => '',
            'PWHT' => '',
        ];
    }

    public function createCsvTemplate(): string
    {
        $handle = fopen('php://temp', 'wb');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $this->headers(), ';');
        fputcsv($handle, array_values($this->sampleRow()), ';');
        rewind($handle);

        $content = stream_get_contents($handle);
        fclose($handle);

        return $content === false ? '' : $content;
    }

    public function createXlsxTemplate(): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'ndt_request_template_');
        if ($tmpPath === false) {
            throw new RuntimeException('Unable to create a temporary file.');
        }

        $archive = new ZipArchive();
        if ($archive->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create XLSX archive.');
        }

        $this->writeXlsxArchive($archive);
        $archive->close();

        $content = file_get_contents($tmpPath);
        @unlink($tmpPath);

        if ($content === false) {
            throw new RuntimeException('Unable to read XLSX archive.');
        }

        return $content;
    }

    /**
     * @return array<int, NdtRequestWeldData>
     */
    public function parseUploadedFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'csv' => $this->parseCsv($file->getRealPath() ?: $file->path()),
            'xlsx' => $this->parseXlsx($file->getRealPath() ?: $file->path()),
            default => throw new RuntimeException('Поддерживаются только CSV и XLSX файлы.'),
        };
    }

    /**
     * @return array<int, NdtRequestWeldData>
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to read CSV file.');
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [];
        }

        $delimiter = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter);
        if ($headers === false) {
            fclose($handle);
            return [];
        }

        $headers = array_map(
            static function (string $header): string {
                $header = trim($header);

                return preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
            },
            $headers,
        );
        $rows = [];

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($values === [null] || count(array_filter($values, static fn ($value) => $value !== null && trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[] = $this->mapRow($headers, $values);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * @return array<int, NdtRequestWeldData>
     */
    private function parseXlsx(string $path): array
    {
        $archive = new ZipArchive();
        if ($archive->open($path) !== true) {
            throw new RuntimeException('Unable to open XLSX file.');
        }

        $sharedStrings = $this->loadSharedStrings($archive->getFromName('xl/sharedStrings.xml') ?: null);
        $sheetPath = $this->firstSheetPath($archive);
        $sheetXml = $archive->getFromName($sheetPath);

        if ($sheetXml === false) {
            $archive->close();
            throw new RuntimeException('Worksheet not found in XLSX file.');
        }

        $archive->close();

        $xml = simplexml_load_string($sheetXml);
        if ($xml === false) {
            throw new RuntimeException('Unable to parse XLSX worksheet.');
        }

        $rows = [];
        $headers = [];
        $firstRow = true;

        $namespaces = $xml->getNamespaces(true);
        $namespace = $namespaces[''] ?? null;
        $sheetData = $namespace !== null ? $xml->children($namespace)->sheetData ?? null : ($xml->sheetData ?? null);
        if ($sheetData === null) {
            throw new RuntimeException('Worksheet data is missing in XLSX file.');
        }

        foreach ($sheetData->row as $row) {
            $values = $this->readXlsxRow($row, $sharedStrings, $namespaces);
            if ($values === []) {
                continue;
            }

            if ($firstRow) {
                $headers = array_map(
                    static function (string $header): string {
                        $header = trim($header);

                        return preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
                    },
                    $values,
                );
                $firstRow = false;

                continue;
            }

            $rows[] = $this->mapRow($headers, $values);
        }

        return $rows;
    }

    /**
     * @return array<int, string>
     */
    private function loadSharedStrings(?string $xml): array
    {
        if ($xml === null || $xml === '') {
            return [];
        }

        $document = simplexml_load_string($xml);
        if ($document === false) {
            return [];
        }

        $strings = [];
        $namespaces = $document->getNamespaces(true);
        $namespace = $namespaces[''] ?? null;
        $shared = $namespace !== null ? $document->children($namespace)->si ?? [] : ($document->si ?? []);
        foreach ($shared as $item) {
            $strings[] = $namespace !== null
                ? trim((string) ($item->children($namespace)->t ?? ''))
                : trim((string) ($item->t ?? ''));
        }

        return $strings;
    }

    /**
     * @return array<int, string>
     */
    private function readXlsxRow(\SimpleXMLElement $row, array $sharedStrings, array $namespaces): array
    {
        $values = [];

        foreach ($row->c as $cell) {
            $reference = (string) $cell['r'];
            $columnIndex = $this->columnIndexFromReference($reference);
            $value = '';

            $type = (string) $cell['t'];
            if ($type === 's') {
                $sharedIndex = (int) ((string) $cell->v);
                $value = $sharedStrings[$sharedIndex] ?? '';
            } elseif ($type === 'inlineStr') {
                $namespace = $namespaces[''] ?? null;
                $inline = $namespace !== null ? $cell->children($namespace)->is ?? null : ($cell->is ?? null);
                $value = $inline !== null
                    ? ($namespace !== null
                        ? trim((string) ($inline->children($namespace)->t ?? ''))
                        : trim((string) ($inline->t ?? '')))
                    : '';
            } else {
                $value = trim((string) $cell->v);
            }

            $values[$columnIndex] = $value;
        }

        if ($values === []) {
            return [];
        }

        ksort($values);

        return array_values($values);
    }

    private function firstSheetPath(ZipArchive $archive): string
    {
        $workbook = $archive->getFromName('xl/workbook.xml');
        $relationships = $archive->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbook === false || $relationships === false) {
            throw new RuntimeException('Workbook metadata is missing.');
        }

        $workbookXml = simplexml_load_string($workbook);
        $relationshipsXml = simplexml_load_string($relationships);

        if ($workbookXml === false || $relationshipsXml === false) {
            throw new RuntimeException('Unable to parse workbook metadata.');
        }

        $namespaces = $workbookXml->getNamespaces(true);
        $namespace = $namespaces[''] ?? null;
        $sheet = $namespace !== null ? $workbookXml->children($namespace)->sheets->sheet[0] ?? null : ($workbookXml->sheets->sheet[0] ?? null);
        if ($sheet === null) {
            throw new RuntimeException('Workbook does not contain worksheets.');
        }

        $relationshipId = (string) $sheet['r:id'];
        $relationshipsNamespace = $relationshipsXml->getNamespaces(true);
        $relationshipsRoot = ($relationshipsNamespace[''] ?? null) !== null
            ? $relationshipsXml->children($relationshipsNamespace[''])
            : $relationshipsXml;

        foreach ($relationshipsRoot->Relationship as $relationship) {
            if ((string) $relationship['Id'] !== $relationshipId) {
                continue;
            }

            $target = ltrim((string) $relationship['Target'], '/');
            if (! str_starts_with($target, 'xl/')) {
                $target = 'xl/'.$target;
            }

            return $target;
        }

        throw new RuntimeException('Unable to resolve the first worksheet path.');
    }

    private function columnIndexFromReference(string $reference): int
    {
        preg_match('/^[A-Z]+/', $reference, $matches);
        $letters = $matches[0] ?? 'A';

        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return $index;
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $values
     */
    private function mapRow(array $headers, array $values): NdtRequestWeldData
    {
        $row = [];
        foreach ($headers as $index => $header) {
            $row[$this->mapHeader($header)] = $values[$index] ?? null;
        }

        return NdtRequestWeldData::fromArray($row);
    }

    private function mapHeader(string $header): string
    {
        return match ($header) {
            'Номер стыка' => 'weld_number',
            'Диаметр' => 'diameter',
            'Толщина' => 'thickness',
            'Дата сварки' => 'welded_at',
            'PWHT' => 'pwht',
            default => strtolower(str_replace([' ', '/', '\\'], '_', trim($header))),
        };
    }

    private function writeXlsxArchive(ZipArchive $archive): void
    {
        $headers = $this->headers();
        $sampleRow = $this->sampleRow();

        $archive->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>
XML);

        $archive->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $archive->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Шаблон заявки НК" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML);

        $archive->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML);

        $archive->addFromString('xl/worksheets/sheet1.xml', $this->buildWorksheetXml($headers, $sampleRow));
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<string, string>  $sampleRow
     */
    private function buildWorksheetXml(array $headers, array $sampleRow): string
    {
        $rows = [];
        $rows[] = $this->worksheetRowXml(1, $headers);
        $rows[] = $this->worksheetRowXml(2, array_values(array_map(
            static fn (string $header): string => $sampleRow[$header] ?? '',
            $headers,
        )));

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .'</worksheet>';
    }

    /**
     * @param  array<int, string>  $values
     */
    private function worksheetRowXml(int $rowNumber, array $values): string
    {
        $cells = [];
        foreach ($values as $index => $value) {
            $cellReference = $this->columnReference($index + 1).$rowNumber;
            $escaped = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $cells[] = '<c r="'.$cellReference.'" t="inlineStr"><is><t>'.$escaped.'</t></is></c>';
        }

        return '<row r="'.$rowNumber.'">'.implode('', $cells).'</row>';
    }

    private function columnReference(int $index): string
    {
        $reference = '';
        while ($index > 0) {
            $index--;
            $reference = chr(($index % 26) + 65).$reference;
            $index = intdiv($index, 26);
        }

        return $reference;
    }
}
