<?php

declare(strict_types=1);

namespace App\Modules\Api\Http\Controllers;

use App\Modules\Api\Http\Resources\FileResource;
use App\Modules\Documents\Http\Requests\StoreFileRequest;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\File;
use App\Modules\Documents\Services\FileService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class MobileFilesController extends ApiController
{
    public function store(StoreFileRequest $request, FileService $files): JsonResponse
    {
        $related = $this->resolveRelatedModel($request);

        $file = $files->store(
            upload: $request->file('file'),
            actor: $request->user(),
            related: $related,
        );

        return $this->created(new FileResource($file), 'Файл загружен.');
    }

    public function download(Request $request, File $file, FileService $files): StreamedResponse
    {
        $this->authorize('download', $file);

        return $files->download($file);
    }

    public function destroy(Request $request, File $file, FileService $files): JsonResponse
    {
        $this->authorize('delete', $file);

        $files->annul(
            file: $file,
            actor: $request->user(),
            reason: $request->input('reason'),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $this->success([
            'deleted' => true,
        ], 'Файл аннулирован.');
    }

    private function resolveRelatedModel(StoreFileRequest $request): Model|Document|null
    {
        if ($request->filled('document_id')) {
            $document = Document::query()->findOrFail((int) $request->input('document_id'));
            $this->authorize('manage', $document);

            return $document;
        }

        if ($request->filled('related_type') && $request->filled('related_id')) {
            $relatedClass = (string) $request->input('related_type');
            $relatedId = (int) $request->input('related_id');

            if (! class_exists($relatedClass)) {
                abort(422, 'Связанная сущность не найдена.');
            }

            /** @var Model $related */
            $related = $relatedClass::query()->findOrFail($relatedId);
            Gate::authorize('manage', $related);

            return $related;
        }

        return null;
    }
}
