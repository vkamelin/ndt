<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Http\Requests\StoreFileRequest;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\File;
use App\Modules\Documents\Services\DocumentService;
use App\Modules\Documents\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class FileController extends Controller
{
    public function store(StoreFileRequest $request, FileService $fileService, DocumentService $documentService): RedirectResponse
    {
        $document = null;
        $related = null;

        if ($request->filled('document_id')) {
            $document = Document::query()->findOrFail((int) $request->input('document_id'));
            $this->authorize('manage', $document);
            $related = $document;
        } elseif ($request->filled('related_type') && $request->filled('related_id')) {
            $relatedClass = (string) $request->input('related_type');
            $relatedId = (int) $request->input('related_id');

            if (! class_exists($relatedClass)) {
                abort(422, 'Связанная сущность не найдена.');
            }

            /** @var \Illuminate\Database\Eloquent\Model $related */
            $related = $relatedClass::query()->findOrFail($relatedId);
            Gate::authorize('manage', $related);
        }

        $file = $fileService->store(
            upload: $request->file('file'),
            actor: $request->user(),
            related: $related,
        );

        if ($document !== null) {
            $documentService->attachFile(
                document: $document,
                file: $file,
                actor: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return back()->with('status', 'Файл прикреплен к документу.');
        }

        return back()->with('status', 'Файл загружен.');
    }

    public function download(Request $request, File $file, FileService $fileService): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $this->authorize('download', $file);

        return $fileService->download($file);
    }

    public function destroy(Request $request, File $file, FileService $fileService): RedirectResponse
    {
        $this->authorize('delete', $file);

        $fileService->annul(
            file: $file,
            actor: $request->user(),
            reason: $request->input('reason'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Файл аннулирован.');
    }
}
