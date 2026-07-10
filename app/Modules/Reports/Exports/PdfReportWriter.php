<?php

declare(strict_types=1);

namespace App\Modules\Reports\Exports;

final class PdfReportWriter
{
    /**
     * @param  list<string>  $lines
     */
    public function build(array $lines): string
    {
        $escapedLines = array_map(static fn (string $line): string => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line), $lines);
        $contentLines = ['BT', '/F1 11 Tf', '50 800 Td'];

        foreach ($escapedLines as $index => $line) {
            if ($index > 0) {
                $contentLines[] = 'T*';
            }

            $contentLines[] = sprintf('(%s) Tj', $line);
        }

        $contentLines[] = 'ET';
        $contentStream = implode("\n", $contentLines);

        $objects = [
            "<< /Type /Catalog /Pages 2 0 R >>",
            "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>",
            "<< /Length ".strlen($contentStream)." >>\nstream\n{$contentStream}\nendstream",
            "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1).' 0 obj'."\n".$object."\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= sprintf("%010d 65535 f \n", 0);

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }
}
