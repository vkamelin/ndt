<?php

declare(strict_types=1);

namespace App\Modules\Registers\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Models\ActType;
use App\Modules\Admin\Models\RegisterType;
use App\Modules\Documents\Http\Requests\StoreFileRequest;
use App\Modules\Documents\Services\FileService;
use App\Modules\Employees\Models\Employee;
use App\Modules\Objects\Models\City;
use App\Modules\Objects\Models\NdtObject;
use App\Modules\Registers\Enums\TransferRegisterStatus;
use App\Modules\Registers\Http\Requests\StoreActRequest;
use App\Modules\Registers\Http\Requests\StoreArchiveCaseItemRequest;
use App\Modules\Registers\Http\Requests\StoreArchiveCaseRequest;
use App\Modules\Registers\Http\Requests\StoreTransferRegisterItemRequest;
use App\Modules\Registers\Http\Requests\StoreTransferRegisterRequest;
use App\Modules\Registers\Http\Requests\UpdateTransferRegisterRequest;
use App\Modules\Registers\Http\Requests\UpdateTransferRegisterStatusRequest;
use App\Modules\Registers\Jobs\ExportActExcelJob;
use App\Modules\Registers\Jobs\ExportTransferRegisterExcelJob;
use App\Modules\Registers\Jobs\GenerateActPdfJob;
use App\Modules\Registers\Jobs\GenerateTransferRegisterPdfJob;
use App\Modules\Registers\Models\Act;
use App\Modules\Registers\Models\ArchiveCase;
use App\Modules\Registers\Models\TransferRegister;
use App\Modules\Registers\Services\ArchiveService;
use App\Modules\Registers\Services\TransferRegisterService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class TransferRegisterController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TransferRegister::class);

        $user = $request->user();
        $objectId = $user?->objectId();
        $isAdmin = $user?->hasRole('Администратор системы') ?? false;

        $registers = TransferRegister::query()
            ->with(['type', 'city', 'object.city', 'senderEmployee.object.city', 'receiverEmployee.object.city'])
            ->withCount(['items', 'acts', 'archiveCases', 'files'])
            ->when(! $isAdmin, function ($query) use ($objectId): void {
                if ($objectId === null) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->where('object_id', $objectId);
            })
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('number', 'like', '%'.$search.'%')
                        ->orWhere('comment', 'like', '%'.$search.'%');
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('register_type_id'), function ($query) use ($request): void {
                $query->where('register_type_id', (int) $request->input('register_type_id'));
            });

        return view('modules.registers.index', [
            'registers' => $registers->orderByDesc('date')->orderByDesc('id')->paginate(15)->withQueryString(),
            'registerTypes' => RegisterType::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => TransferRegisterStatus::options(),
            'cities' => City::query()->orderBy('name')->get(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'employees' => Employee::query()->with('object.city')->orderBy('last_name')->get(),
        ]);
    }

    public function show(Request $request, TransferRegister $transferRegister): View
    {
        $this->authorize('view', $transferRegister);

        $transferRegister->load([
            'type',
            'city',
            'object.city',
            'senderEmployee.object.city',
            'receiverEmployee.object.city',
            'items.related',
            'items.file',
            'acts.type',
            'acts.files',
            'archiveCases.items.related',
            'archiveCases.items.file',
            'archiveCases.register',
            'files.uploadedBy',
        ]);

        return view('modules.registers.show', [
            'register' => $transferRegister,
            'registerTypes' => RegisterType::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => TransferRegisterStatus::options(),
            'cities' => City::query()->orderBy('name')->get(),
            'objects' => NdtObject::query()->with('city')->orderBy('name')->get(),
            'employees' => Employee::query()->with('object.city')->orderBy('last_name')->get(),
            'actTypes' => ActType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTransferRegisterRequest $request, TransferRegisterService $service): RedirectResponse
    {
        $register = $service->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.registers.show', $register)->with('status', 'Реестр создан.');
    }

    public function update(UpdateTransferRegisterRequest $request, TransferRegister $transferRegister, TransferRegisterService $service): RedirectResponse
    {
        $service->update(
            register: $transferRegister,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Реестр обновлен.');
    }

    public function updateStatus(UpdateTransferRegisterStatusRequest $request, TransferRegister $transferRegister, TransferRegisterService $service): RedirectResponse
    {
        $status = $request->string('status')->toString();
        $comment = $request->string('comment')->toString();

        match ($status) {
            TransferRegisterStatus::Formed->value => $service->form($transferRegister, $request->user(), $comment !== '' ? $comment : null, $request->ip(), $request->userAgent()),
            TransferRegisterStatus::Sent->value => $service->send($transferRegister, $request->user(), $comment !== '' ? $comment : null, $request->ip(), $request->userAgent()),
            TransferRegisterStatus::Accepted->value => $service->accept($transferRegister, $request->user(), $comment !== '' ? $comment : null, $request->ip(), $request->userAgent()),
            TransferRegisterStatus::Returned->value => $service->returnRegister($transferRegister, $request->user(), $comment !== '' ? $comment : null, $request->ip(), $request->userAgent()),
            TransferRegisterStatus::Closed->value => $service->close($transferRegister, $request->user(), $comment !== '' ? $comment : null, $request->ip(), $request->userAgent()),
            default => abort(422, 'Неподдерживаемый статус.'),
        };

        return back()->with('status', 'Статус реестра обновлен.');
    }

    public function storeItem(StoreTransferRegisterItemRequest $request, TransferRegister $transferRegister, TransferRegisterService $service): RedirectResponse
    {
        $service->addItem(
            register: $transferRegister,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Позиция реестра добавлена.');
    }

    public function storeFile(StoreFileRequest $request, TransferRegister $transferRegister, FileService $fileService): RedirectResponse
    {
        $related = $transferRegister;

        if ($request->filled('related_type') && $request->filled('related_id')) {
            $relatedClass = (string) $request->input('related_type');
            $relatedId = (int) $request->input('related_id');

            if (! class_exists($relatedClass)) {
                abort(422, 'Связанная сущность не найдена.');
            }

            /** @var Model $related */
            $related = $relatedClass::query()->findOrFail($relatedId);
            $this->authorize('manage', $related);
        } else {
            $this->authorize('manage', $transferRegister);
        }

        $fileService->store(
            upload: $request->file('file'),
            actor: $request->user(),
            related: $related,
        );

        return back()->with('status', 'Файл загружен.');
    }

    public function storeAct(StoreActRequest $request, TransferRegister $transferRegister, TransferRegisterService $service): RedirectResponse
    {
        $service->createAct(
            register: $transferRegister,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Акт создан.');
    }

    public function storeArchiveCase(StoreArchiveCaseRequest $request, TransferRegister $transferRegister, ArchiveService $archiveService): RedirectResponse
    {
        $archiveService->create(
            data: [
                'transfer_register_id' => $transferRegister->getKey(),
                ...$request->validated(),
            ],
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Архивное дело создано.');
    }

    public function storeArchiveCaseItem(StoreArchiveCaseItemRequest $request, ArchiveCase $archiveCase, ArchiveService $archiveService): RedirectResponse
    {
        $archiveService->addItem(
            archiveCase: $archiveCase,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Архивная позиция добавлена.');
    }

    public function exportPdf(Request $request, TransferRegister $transferRegister): RedirectResponse
    {
        $this->authorize('view', $transferRegister);

        GenerateTransferRegisterPdfJob::dispatch(
            transferRegisterId: $transferRegister->getKey(),
            actorId: $request->user()?->getKey(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'PDF реестра поставлен в очередь.');
    }

    public function exportExcel(Request $request, TransferRegister $transferRegister): RedirectResponse
    {
        $this->authorize('view', $transferRegister);

        ExportTransferRegisterExcelJob::dispatch(
            transferRegisterId: $transferRegister->getKey(),
            actorId: $request->user()?->getKey(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Excel реестра поставлен в очередь.');
    }

    public function exportActPdf(Request $request, Act $act): RedirectResponse
    {
        $this->authorize('manage', $act);

        GenerateActPdfJob::dispatch(
            actId: $act->getKey(),
            actorId: $request->user()?->getKey(),
        );

        return back()->with('status', 'PDF акта поставлен в очередь.');
    }

    public function exportActExcel(Request $request, Act $act): RedirectResponse
    {
        $this->authorize('manage', $act);

        ExportActExcelJob::dispatch(
            actId: $act->getKey(),
            actorId: $request->user()?->getKey(),
        );

        return back()->with('status', 'Excel акта поставлен в очередь.');
    }
}
