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

final class GenerateTransferRegisterPdfJob implements ShouldQueue
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
        $lines = [
            'Transfer register #'.$register->number,
            'Date: '.$register->date?->toDateString(),
            'Type: '.$register->type?->name,
            'Object: '.$register->object?->name,
            'Status: '.$register->status->label(),
            'Sender: '.$register->senderEmployee?->fullName(),
            'Receiver: '.$register->receiverEmployee?->fullName(),
            'Items:',
        ];

        foreach ($register->items as $item) {
            $lines[] = sprintf(
                '%d. %s #%d',
                $item->sort_order,
                class_basename($item->related_type),
                $item->related_id,
            );
        }

        $content = $this->buildPdf($lines);
        $disk = config('filesystems.default', 'private');
        $storageName = (string) Str::uuid().'.pdf';
        $storagePath = 'registers/'.now()->format('Y/m/d').'/'.$storageName;

        Storage::disk($disk)->put($storagePath, $content);

        File::query()->create([
            'original_name' => $register->number.'.pdf',
            'storage_name' => $storageName,
            'storage_path' => $storagePath,
            'disk' => $disk,
            'mime_type' => 'application/pdf',
            'size' => strlen($content),
            'hash' => hash('sha256', $content),
            'uploaded_by_user_id' => $actor?->getKey(),
            'related_type' => $register::class,
            'related_id' => $register->getKey(),
            'status' => FileStatus::Active->value,
        ]);
    }

    /**
     * @param  list<string>  $lines
     */
    private function buildPdf(array $lines): string
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
