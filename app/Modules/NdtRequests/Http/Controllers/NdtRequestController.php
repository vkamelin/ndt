<?php

declare(strict_types=1);

namespace App\Modules\NdtRequests\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\Title;
use App\Modules\NdtRequests\DTO\NdtRequestFormData;
use App\Modules\NdtRequests\DTO\NdtRequestWeldData;
use App\Modules\NdtRequests\Enums\NdtRequestStatus;
use App\Modules\NdtRequests\Http\Requests\ConfirmNdtRequestImportRequest;
use App\Modules\NdtRequests\Http\Requests\StoreNdtRequestImportRequest;
use App\Modules\NdtRequests\Http\Requests\StoreNdtRequestWithWeldsRequest;
use App\Modules\NdtRequests\Http\Requests\UpdateNdtRequestRequest;
use App\Modules\NdtRequests\Http\Requests\UpdateNdtRequestStatusRequest;
use App\Modules\NdtRequests\Models\NdtRequest;
use App\Modules\NdtRequests\Services\NdtRequestImportService;
use App\Modules\NdtRequests\Services\NdtRequestService;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Welds\Models\Weld;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class NdtRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', NdtRequest::class);

        $requests = NdtRequest::query()
            ->with(['organization', 'object.city', 'title'])
            ->withCount('welds')
            ->when(! ($request->user()?->hasRole('Администратор системы') ?? false), function ($query) use ($request): void {
                $objectId = $request->user()?->objectId();

                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $query->where('request_number', 'like', '%'.$request->string('search')->toString().'%');
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('object_id'), function ($query) use ($request): void {
                $query->where('object_id', (int) $request->input('object_id'));
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('modules.ndt-requests.index', [
            'requests' => $requests,
            'statuses' => NdtRequestStatus::options(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->can('ndt_requests.manage') ?? false, 403);

        $currentObject = $this->currentObject($request);

        return view('modules.ndt-requests.create', [
            'currentObject' => $currentObject,
            'objects' => $request->user()?->hasRole('Администратор системы') ? NdtObject::query()->with(['city', 'organization'])->orderBy('name')->get() : collect([$currentObject])->filter(),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreNdtRequestWithWeldsRequest $request, NdtRequestService $ndtRequests): RedirectResponse
    {
        $ndtRequests->createWithWelds(
            requestData: NdtRequestFormData::fromArray($request->validated()),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.ndt-requests.index')->with('status', 'Заявка создана и стыки зарегистрированы.');
    }

    public function import(Request $request): View
    {
        abort_unless($request->user()?->can('ndt_requests.manage') ?? false, 403);

        $currentObject = $this->currentObject($request);
        $preview = null;
        $importToken = null;

        if ($request->filled('import_token')) {
            $importToken = (string) $request->string('import_token');
            $preview = $this->loadPreview($importToken);
        }

        return view('modules.ndt-requests.import', [
            'currentObject' => $currentObject,
            'objects' => $request->user()?->hasRole('Администратор системы') ? NdtObject::query()->with(['city', 'organization'])->orderBy('name')->get() : collect([$currentObject])->filter(),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
            'preview' => $preview,
            'importToken' => $importToken,
        ]);
    }

    public function previewImport(StoreNdtRequestImportRequest $request, NdtRequestImportService $imports): View|RedirectResponse
    {
        try {
            $parsedRows = $imports->parseUploadedFile($request->file('file'));
            $this->validateImportRows($parsedRows);
        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors(['file' => $exception->getMessage()])
                ->withInput();
        }

        $token = (string) Str::uuid();

        $payload = [
            'request' => [
                'request_number' => $request->validated('request_number'),
                'request_date' => $request->validated('request_date'),
                'organization_id' => $request->validated('organization_id'),
                'object_id' => (int) $request->validated('object_id'),
                'title_id' => $request->validated('title_id'),
                'priority' => $request->validated('priority'),
                'due_date' => $request->validated('due_date'),
                'basis' => $request->validated('basis'),
                'comment' => $request->validated('comment'),
            ],
            'rows' => array_map(static fn (NdtRequestWeldData $row): array => $row->toArray(), $parsedRows),
        ];

        Storage::disk('local')->put($this->importTokenPath($token), json_encode($payload, JSON_THROW_ON_ERROR));

        return view('modules.ndt-requests.import', [
            'currentObject' => $this->currentObject($request),
            'objects' => $request->user()?->hasRole('Администратор системы') ? NdtObject::query()->with(['city', 'organization'])->orderBy('name')->get() : collect([$this->currentObject($request)])->filter(),
            'organizations' => Organization::query()->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
            'preview' => $payload,
            'importToken' => $token,
        ]);
    }

    public function storeImport(ConfirmNdtRequestImportRequest $request, NdtRequestService $ndtRequests): RedirectResponse
    {
        $payload = $this->loadPreview((string) $request->validated('import_token'));

        if ($payload === null) {
            return redirect()->route('admin.ndt-requests.import')->withErrors(['import_token' => 'Срок подготовки импорта истек.']);
        }

        $requestData = NdtRequestFormData::fromArray($payload['request'] + ['welds' => $payload['rows']]);

        $ndtRequests->createWithWelds(
            requestData: $requestData,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        Storage::disk('local')->delete($this->importTokenPath((string) $request->validated('import_token')));

        return redirect()->route('admin.ndt-requests.index')->with('status', 'Заявка импортирована и стыки зарегистрированы.');
    }

    public function sampleCsv(NdtRequestImportService $imports): StreamedResponse
    {
        abort_unless(request()->user()?->can('ndt_requests.manage') ?? false, 403);

        return response()->streamDownload(
            static function () use ($imports): void {
                echo $imports->createCsvTemplate();
            },
            'ndt-request-template.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function sampleXlsx(NdtRequestImportService $imports): StreamedResponse
    {
        abort_unless(request()->user()?->can('ndt_requests.manage') ?? false, 403);

        return response()->streamDownload(
            static function () use ($imports): void {
                echo $imports->createXlsxTemplate();
            },
            'ndt-request-template.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function show(NdtRequest $ndtRequest): View
    {
        $this->authorize('view', $ndtRequest);

        $ndtRequest->load(['organization', 'object.city', 'title', 'welds.object.city', 'statusHistory.changedBy']);

        return view('modules.ndt-requests.show', [
            'request' => $ndtRequest,
            'organizations' => Organization::query()->orderBy('name')->get(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'titles' => Title::query()->orderBy('name')->get(),
            'welds' => Weld::query()
                ->with(['object.city'])
                ->where('object_id', $ndtRequest->object_id)
                ->orderByDesc('id')
                ->get(),
            'statuses' => NdtRequestStatus::options(),
        ]);
    }

    public function update(UpdateNdtRequestRequest $request, NdtRequest $ndtRequest, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $ndtRequests->update(
            request: $ndtRequest,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Заявка обновлена.');
    }

    public function updateStatus(UpdateNdtRequestStatusRequest $request, NdtRequest $ndtRequest, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $ndtRequests->updateStatus(
            request: $ndtRequest,
            status: NdtRequestStatus::from($request->validated('status')),
            comment: $request->validated('comment') ?? null,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Статус заявки обновлен.');
    }

    public function attachWeld(Request $request, NdtRequest $ndtRequest, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $weld = Weld::query()->findOrFail((int) $request->input('weld_id'));

        $ndtRequests->attachWeld(
            request: $ndtRequest,
            weld: $weld,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Стык добавлен в заявку.');
    }

    public function detachWeld(Request $request, NdtRequest $ndtRequest, Weld $weld, NdtRequestService $ndtRequests): RedirectResponse
    {
        $this->authorize('manage', $ndtRequest);

        $ndtRequests->detachWeld(
            request: $ndtRequest,
            weld: $weld,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Стык удален из заявки.');
    }

    private function currentObject(Request $request): ?NdtObject
    {
        $objectId = $request->user()?->objectId();
        if ($objectId === null) {
            return null;
        }

        return NdtObject::query()->with('organization')->find($objectId);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadPreview(string $token): ?array
    {
        $path = $this->importTokenPath($token);
        if (! Storage::disk('local')->exists($path)) {
            return null;
        }

        $decoded = json_decode((string) Storage::disk('local')->get($path), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function importTokenPath(string $token): string
    {
        return 'ndt-request-imports/'.$token.'.json';
    }

    /**
     * @param  array<int, NdtRequestWeldData>  $rows
     */
    private function validateImportRows(array $rows): void
    {
        foreach ($rows as $index => $row) {
            $validator = Validator::make($row->toArray(), [
                'weld_number' => ['required', 'string', 'max:255'],
                'diameter' => ['nullable', 'numeric'],
                'thickness' => ['nullable', 'numeric'],
                'welded_at' => ['nullable', 'date'],
                'pwht' => ['nullable', 'boolean'],
            ]);

            if ($validator->fails()) {
                throw ValidationException::withMessages([
                    'file' => 'Строка '.($index + 2).': '.$validator->errors()->first(),
                ]);
            }
        }
    }
}
