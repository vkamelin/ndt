<?php

declare(strict_types=1);

namespace App\Modules\Reports\Exports;

use RuntimeException;
use ZipArchive;

final class ExcelReportWriter
{
    /**
     * @param  list<string>  $headers
     * @param  list<list<string|null>>  $rows
     */
    public function build(string $sheetName, array $headers, array $rows): string
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'report_xlsx_');
        if ($tmpPath === false) {
            throw new RuntimeException('Unable to create temporary XLSX file.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tmpPath, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create XLSX archive.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->relsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($headers, $rows));
        $zip->close();

        $content = file_get_contents($tmpPath);
        unlink($tmpPath);

        if ($content === false) {
            throw new RuntimeException('Unable to read XLSX archive.');
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

    private function workbookXml(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.htmlspecialchars($sheetName, ENT_XML1 | ENT_COMPAT, 'UTF-8').'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
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
     * @param  list<string>  $headers
     * @param  list<list<string|null>>  $rows
     */
    private function sheetXml(array $headers, array $rows): string
    {
        $xmlRows = [];
        $xmlRows[] = $this->xmlRow(1, $headers);

        $rowNumber = 2;
        foreach ($rows as $row) {
            $xmlRows[] = $this->xmlRow($rowNumber, $row);
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
     * @param  list<string|null>  $values
     */
    private function xmlRow(int $rowNumber, array $values): string
    {
        $cells = [];

        foreach ($values as $index => $value) {
            $cellRef = $this->columnLetter($index + 1).$rowNumber;
            $cells[] = '<c r="'.$cellRef.'" t="inlineStr"><is><t>'.htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_COMPAT, 'UTF-8').'</t></is></c>';
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
