<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Services;

use App\Models\User;
use App\Modules\Audit\Concerns\RecordsAuditLogs;
use App\Modules\Audit\DTO\AuditData;
use App\Modules\Organizations\Models\Laboratory;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Models\OrganizationContact;

final class OrganizationService
{
    use RecordsAuditLogs;

    /**
     * @param  array{name: string, comment?: string|null, is_active?: bool}  $data
     */
    public function create(array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Organization
    {
        $organization = Organization::query()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Organization::class,
                entityId: $organization->getKey(),
                operation: 'organization.created',
                after: $this->snapshot($organization),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $organization;
    }

    /**
     * @param  array{name?: string, comment?: string|null, is_active?: bool}  $data
     */
    public function update(Organization $organization, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Organization
    {
        $before = $this->snapshot($organization);
        $organization->fill($this->normalize($data))->save();
        $organization->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Organization::class,
                entityId: $organization->getKey(),
                operation: 'organization.updated',
                before: $before,
                after: $this->snapshot($organization),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $organization;
    }

    public function deactivate(Organization $organization, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Organization
    {
        if (! $organization->is_active) {
            return $organization;
        }

        return $this->update($organization, ['is_active' => false], $actor, $ipAddress, $userAgent);
    }

    /**
     * @param  array{name: string, position?: string|null, phone?: string|null, email?: string|null, is_primary?: bool, comment?: string|null}  $data
     */
    public function addContact(Organization $organization, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): OrganizationContact
    {
        $contact = $organization->contacts()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: OrganizationContact::class,
                entityId: $contact->getKey(),
                operation: 'organization_contact.created',
                after: $this->contactSnapshot($contact),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $contact;
    }

    /**
     * @param  array{name?: string, position?: string|null, phone?: string|null, email?: string|null, is_primary?: bool, comment?: string|null}  $data
     */
    public function updateContact(OrganizationContact $contact, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): OrganizationContact
    {
        $before = $this->contactSnapshot($contact);
        $contact->fill($this->normalize($data))->save();
        $contact->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: OrganizationContact::class,
                entityId: $contact->getKey(),
                operation: 'organization_contact.updated',
                before: $before,
                after: $this->contactSnapshot($contact),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $contact;
    }

    public function removeContact(OrganizationContact $contact, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $before = $this->contactSnapshot($contact);
        $contact->delete();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: OrganizationContact::class,
                entityId: $contact->getKey(),
                operation: 'organization_contact.deleted',
                before: $before,
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );
    }

    /**
     * @param  array{name: string, comment?: string|null, is_active?: bool}  $data
     */
    public function addLaboratory(Organization $organization, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Laboratory
    {
        $laboratory = $organization->laboratories()->create($this->normalize($data));

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Laboratory::class,
                entityId: $laboratory->getKey(),
                operation: 'laboratory.created',
                after: $this->laboratorySnapshot($laboratory),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $laboratory;
    }

    /**
     * @param  array{name?: string, comment?: string|null, is_active?: bool}  $data
     */
    public function updateLaboratory(Laboratory $laboratory, array $data, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): Laboratory
    {
        $before = $this->laboratorySnapshot($laboratory);
        $laboratory->fill($this->normalize($data))->save();
        $laboratory->refresh();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Laboratory::class,
                entityId: $laboratory->getKey(),
                operation: 'laboratory.updated',
                before: $before,
                after: $this->laboratorySnapshot($laboratory),
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );

        return $laboratory;
    }

    public function removeLaboratory(Laboratory $laboratory, ?User $actor = null, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $before = $this->laboratorySnapshot($laboratory);
        $laboratory->delete();

        $this->recordAudit(
            AuditData::forModelChange(
                entityType: Laboratory::class,
                entityId: $laboratory->getKey(),
                operation: 'laboratory.deleted',
                before: $before,
                actor: $actor,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            ),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Organization $organization): array
    {
        return [
            'id' => $organization->getKey(),
            'name' => $organization->name,
            'is_active' => $organization->is_active,
            'comment' => $organization->comment,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contactSnapshot(OrganizationContact $contact): array
    {
        return [
            'id' => $contact->getKey(),
            'organization_id' => $contact->organization_id,
            'name' => $contact->name,
            'position' => $contact->position,
            'phone' => $contact->phone,
            'email' => $contact->email,
            'is_primary' => $contact->is_primary,
            'comment' => $contact->comment,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function laboratorySnapshot(Laboratory $laboratory): array
    {
        return [
            'id' => $laboratory->getKey(),
            'organization_id' => $laboratory->organization_id,
            'name' => $laboratory->name,
            'is_active' => $laboratory->is_active,
            'comment' => $laboratory->comment,
        ];
    }
}
