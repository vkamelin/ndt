<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Organizations\Http\Requests\StoreLaboratoryRequest;
use App\Modules\Organizations\Http\Requests\StoreOrganizationContactRequest;
use App\Modules\Organizations\Http\Requests\StoreOrganizationRequest;
use App\Modules\Organizations\Http\Requests\UpdateLaboratoryRequest;
use App\Modules\Organizations\Http\Requests\UpdateOrganizationContactRequest;
use App\Modules\Organizations\Http\Requests\UpdateOrganizationRequest;
use App\Modules\Organizations\Models\Laboratory;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Models\OrganizationContact;
use App\Modules\Organizations\Services\OrganizationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Organization::class);

        $organizations = Organization::query()
            ->withCount(['contacts', 'laboratories'])
            ->when($request->string('search')->toString() !== '', function ($query) use ($request): void {
                $query->where('name', 'like', '%'.$request->string('search')->toString().'%');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('modules.organizations.index', [
            'organizations' => $organizations,
        ]);
    }

    public function show(Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load(['contacts', 'laboratories']);

        return view('modules.organizations.show', [
            'organization' => $organization,
        ]);
    }

    public function create(): View
    {
        $this->authorize('organizations.manage');

        return view('modules.organizations.create');
    }

    public function edit(Organization $organization): View
    {
        $this->authorize('manage', $organization);

        return view('modules.organizations.edit', [
            'organization' => $organization,
        ]);
    }

    public function store(StoreOrganizationRequest $request, OrganizationService $organizations): RedirectResponse
    {
        $organization = $organizations->create(
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.organizations.show', $organization)->with('status', 'Организация создана.');
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $updatedOrganization = $organizations->update(
            organization: $organization,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return redirect()->route('admin.organizations.show', $updatedOrganization)->with('status', 'Организация обновлена.');
    }

    public function destroy(Request $request, Organization $organization, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $organizations->deactivate(
            organization: $organization,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Организация деактивирована.');
    }

    public function storeContact(StoreOrganizationContactRequest $request, Organization $organization, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $organizations->addContact(
            organization: $organization,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Контакт организации добавлен.');
    }

    public function updateContact(UpdateOrganizationContactRequest $request, Organization $organization, OrganizationContact $contact, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $organizations->updateContact(
            contact: $contact,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Контакт организации обновлен.');
    }

    public function destroyContact(Request $request, Organization $organization, OrganizationContact $contact, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $organizations->removeContact(
            contact: $contact,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Контакт организации удален.');
    }

    public function storeLaboratory(StoreLaboratoryRequest $request, Organization $organization, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $organizations->addLaboratory(
            organization: $organization,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Лаборатория добавлена.');
    }

    public function updateLaboratory(UpdateLaboratoryRequest $request, Organization $organization, Laboratory $laboratory, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $organizations->updateLaboratory(
            laboratory: $laboratory,
            data: $request->validated(),
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Лаборатория обновлена.');
    }

    public function destroyLaboratory(Request $request, Organization $organization, Laboratory $laboratory, OrganizationService $organizations): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $organizations->removeLaboratory(
            laboratory: $laboratory,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return back()->with('status', 'Лаборатория удалена.');
    }
}
