<?php

declare(strict_types=1);

namespace App\Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Documents\Enums\DocumentStatus;
use App\Modules\Documents\Http\Requests\StoreDocumentRequest;
use App\Modules\Documents\Http\Requests\StoreDocumentVersionRequest;
use App\Modules\Documents\Http\Requests\UpdateDocumentRequest;
use App\Modules\Documents\Models\Document;
use App\Modules\Documents\Models\DocumentType;
use App\Modules\Documents\Services\DocumentService;
use App\Modules\Employees\Models\Employee;
use App\Modules\Equipment\Models\Equipment;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $scopeObject = $this->scopeObject($user);
        $scopeCity = $scopeObject?->city;

        $documents = Document::query()
            ->with(['type', 'organization', 'city', 'object.city', 'employee.object.city', 'equipment.object.city', 'request'])
            ->withCount(['versions', 'files'])
            ->when(! $isAdmin, function ($query) use ($scopeObject): void {
                $objectId = $scopeObject?->getKey();
                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where(function ($nested) use ($objectId): void {
                    $nested->where('object_id', $objectId)
                        ->orWhereHas('request', function ($subQuery) use ($objectId): void {
                            $subQuery->where('object_id', $objectId);
                        });
                });
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('number', 'like', '%'.$search.'%')
                        ->orWhere('comment', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('document_type_id'), function ($query) use ($request): void {
                $query->where('document_type_id', (int) $request->input('document_type_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->where('object_id', (int) $request->input('object_id'));
            });

        return view('modules.documents.index', [
            'documents' => $documents->orderByDesc('document_date')->orderByDesc('id')->paginate(15)->withQueryString(),
            'documentTypes' => DocumentType::query()->where('is_active', true)->orderBy('name')->get(),
            'objects' => $isAdmin
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$scopeObject])->filter(),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'cities' => $isAdmin
                ? City::query()->orderBy('name')->get()
                : collect([$scopeCity])->filter(),
            'employees' => $isAdmin
                ? Employee::query()->with('object.city')->orderBy('last_name')->get()
                : Employee::query()->with('object.city')->where('object_id', $scopeObject?->getKey())->orderBy('last_name')->get(),
            'equipment' => $isAdmin
                ? Equipment::query()->with('object.city')->orderBy('name')->get()
                : Equipment::query()->with('object.city')->where('object_id', $scopeObject?->getKey())->orderBy('name')->get(),
            'requests' => $isAdmin
                ? NdtRequest::query()->with('object.city')->orderByDesc('request_date')->get()
                : NdtRequest::query()->with('object.city')->where('object_id', $scopeObject?->getKey())->orderByDesc('request_date')->get(),
            'statuses' => DocumentStatus::options(),
            'isAdmin' => $isAdmin,
            'scopeCity' => $scopeCity,
            'scopeObject' => $scopeObject,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Document::class);

        $user = $request->user();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;
        $scopeObject = $this->scopeObject($user);
        $scopeCity = $scopeObject?->city;

        return view('modules.documents.create', [
            'documentTypes' => DocumentType::query()->where('is_active', true)->orderBy('name')->get(),
            'objects' => $isAdmin
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$scopeObject])->filter(),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'cities' => $isAdmin
                ? City::query()->orderBy('name')->get()
                : collect([$scopeCity])->filter(),
            'employees' => $isAdmin
                ? Employee::query()->with('object.city')->orderBy('last_name')->get()
                : Employee::query()->with('object.city')->where('object_id', $scopeObject?->getKey())->orderBy('last_name')->get(),
            'equipment' => $isAdmin
                ? Equipment::query()->with('object.city')->orderBy('name')->get()
                : Equipment::query()->with('object.city')->where('object_id', $scopeObject?->getKey())->orderBy('name')->get(),
            'requests' => $isAdmin
                ? NdtRequest::query()->with('object.city')->orderByDesc('request_date')->get()
                : NdtRequest::query()->with('object.city')->where('object_id', $scopeObject?->getKey())->orderByDesc('request_date')->get(),
            'statuses' => DocumentStatus::options(),
            'isAdmin' => $isAdmin,
            'scopeCity' => $scopeCity,
            'scopeObject' => $scopeObject,
        ]);
    }

    public function show(Request $request, Document $document): View
    {
        $this->authorize('view', $document);

        $document->load([
            'type',
            'organization',
            'city',
            'object.city',
            'employee.object.city',
            'equipment.object.city',
            'request.object.city',
            'files.uploadedBy',
            'versions.file.uploadedBy',
            'versions.createdBy',
            'relations',
        ]);

        return view('modules.documents.show', [
            'document' => $document,
            'documentTypes' => DocumentType::query()->where('is_active', true)->orderBy('name')->get(),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'cities' => $request->user()?->hasRole('Администратор системы')
                ? City::query()->orderBy('name')->get()
                : collect([$this->scopeObject($request->user())?->city])->filter(),
            'objects' => $request->user()?->hasRole('Администратор системы')
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$this->scopeObject($request->user())])->filter(),
            'employees' => $request->user()?->hasRole('Администратор системы')
                ? Employee::query()->with('object.city')->orderBy('last_name')->get()
                : Employee::query()->with('object.city')->where('object_id', $this->scopeObject($request->user())?->getKey())->orderBy('last_name')->get(),
            'equipment' => $request->user()?->hasRole('Администратор системы')
                ? Equipment::query()->with('object.city')->orderBy('name')->get()
                : Equipment::query()->with('object.city')->where('object_id', $this->scopeObject($request->user())?->getKey())->orderBy('name')->get(),
            'requests' => $request->user()?->hasRole('Администратор системы')
                ? NdtRequest::query()->with('object.city')->orderByDesc('request_date')->get()
                : NdtRequest::query()->with('object.city')->where('object_id', $this->scopeObject($request->user())?->getKey())->orderByDesc('request_date')->get(),
            'statuses' => DocumentStatus::options(),
            'isAdmin' => $request->user()?->hasRole('Администратор системы') ?? false,
            'scopeCity' => $this->scopeObject($request->user())?->city,
            'scopeObject' => $this->scopeObject($request->user()),
        ]);
    }

    public function edit(Request $request, Document $document): View
    {
        $this->authorize('manage', $document);

        $document->load([
            'type',
            'organization',
            'city',
            'object.city',
            'employee.object.city',
            'equipment.object.city',
            'request.object.city',
            'files.uploadedBy',
            'versions.file.uploadedBy',
            'versions.createdBy',
            'relations',
        ]);

        return view('modules.documents.edit', [
            'document' => $document,
            'documentTypes' => DocumentType::query()->where('is_active', true)->orderBy('name')->get(),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'cities' => $request->user()?->hasRole('Администратор системы')
                ? City::query()->orderBy('name')->get()
                : collect([$this->scopeObject($request->user())?->city])->filter(),
            'objects' => $request->user()?->hasRole('Администратор системы')
                ? NdtObject::query()->with('city')->orderBy('name')->get()
                : collect([$this->scopeObject($request->user())])->filter(),
            'employees' => $request->user()?->hasRole('Администратор системы')
                ? Employee::query()->with('object.city')->orderBy('last_name')->get()
                : Employee::query()->with('object.city')->where('object_id', $this->scopeObject($request->user())?->getKey())->orderBy('last_name')->get(),
            'equipment' => $request->user()?->hasRole('Администратор системы')
                ? Equipment::query()->with('object.city')->orderBy('name')->get()
                : Equipment::query()->with('object.city')->where('object_id', $this->scopeObject($request->user())?->getKey())->orderBy('name')->get(),
            'requests' => $request->user()?->hasRole('Администратор системы')
                ? NdtRequest::query()->with('object.city')->orderByDesc('request_date')->get()
                : NdtRequest::query()->with('object.city')->where('object_id', $this->scopeObject($request->user())?->getKey())->orderByDesc('request_date')->get(),
            'statuses' => DocumentStatus::options(),
            'isAdmin' => $request->user()?->hasRole('Администратор системы') ?? false,
            'scopeCity' => $this->scopeObject($request->user())?->city,
            'scopeObject' => $this->scopeObject($request->user()),
        ]);
    }

    public function store(StoreDocumentRequest $request, DocumentService $documentService): RedirectResponse
    {
        $document = $documentService->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.documents.show', $document)->with('status', 'Документ создан.');
    }

    public function update(UpdateDocumentRequest $request, Document $document, DocumentService $documentService): RedirectResponse
    {
        $item = $documentService->update(
            document: $document,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.documents.show', $item)->with('status', 'Документ обновлен.');
    }

    public function storeVersion(StoreDocumentVersionRequest $request, Document $document, DocumentService $documentService): RedirectResponse
    {
        $documentService->addVersion(
            document: $document,
            upload: $request->file('file'),
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Версия документа добавлена.');
    }

    private function scopeObject(?\App\Models\User $user): ?NdtObject
    {
        if ($user === null || $user->hasRole('Администратор системы')) {
            return null;
        }

        $objectId = $user->objectId();
        if ($objectId === null) {
            return null;
        }

        return NdtObject::query()->with('city')->find($objectId);
    }
}
