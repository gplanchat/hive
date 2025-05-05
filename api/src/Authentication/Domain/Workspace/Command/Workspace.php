<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\Query\Workspace as QueryWorkspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceInput;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceWithinOrganizationInput;
use App\Authentication\UserInterface\Workspace\DeleteWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\DisableWorkspaceInput;
use App\Authentication\UserInterface\Workspace\DisableWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\EnableWorkspaceInput;
use App\Authentication\UserInterface\Workspace\EnableWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\QueryOneWorkspaceProvider;

#[Post(
    uriTemplate: '/authentication/workspaces',
    class: QueryWorkspace::class,
    input: CreateWorkspaceInput::class,
    output: QueryWorkspace::class,
    processor: CreateWorkspaceProcessor::class,
    itemUriTemplate: '/authentication/workspaces/{uuid}',
)]
#[Post(
    uriTemplate: '/authentication/organizations/{organizationId}/workspaces',
    uriVariables: ['organizationId'],
    class: QueryWorkspace::class,
    input: CreateWorkspaceWithinOrganizationInput::class,
    output: QueryWorkspace::class,
    processor: CreateWorkspaceProcessor::class,
    itemUriTemplate: '/authentication/workspaces/{uuid}',
)]
#[Patch(
    uriTemplate: '/authentication/workspaces/{uuid}/enable',
    uriVariables: ['uuid'],
    class: QueryWorkspace::class,
    input: EnableWorkspaceInput::class,
    output: QueryWorkspace::class,
    provider: QueryOneWorkspaceProvider::class,
    processor: EnableWorkspaceProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/workspaces/{uuid}/disable',
    uriVariables: ['uuid'],
    class: QueryWorkspace::class,
    input: DisableWorkspaceInput::class,
    output: QueryWorkspace::class,
    provider: QueryOneWorkspaceProvider::class,
    processor: DisableWorkspaceProcessor::class,
)]
#[Delete(
    uriTemplate: '/authentication/workspaces/{uuid}',
    uriVariables: ['uuid'],
    class: QueryWorkspace::class,
    input: false,
    output: false,
    provider: QueryOneWorkspaceProvider::class,
    processor: DeleteWorkspaceProcessor::class,
)]
final class Workspace
{
    public function __construct(
        public readonly WorkspaceId $uuid,
        public readonly OrganizationId $organizationId,
        private ?string $name = null,
        private ?string $slug = null,
        private ?\DateTimeInterface $validUntil = null,
        private bool $enabled = false,
        private bool $deleted = false,
        private array $events = [],
        private int $version = 0,
    ) {
    }

    private function apply(object $event): void
    {
        $methodName = 'apply'.substr(__CLASS__, strrpos(__CLASS__, '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function recordThat(object $event): void
    {
        $this->events[] = $event;
        $this->version++;
        $this->apply($event);
    }

    public function releaseEvents(): array
    {
        $releasedEvents = $this->events;
        $this->events = [];
        return $releasedEvents;
    }

    public static function declareEnabled(
        WorkspaceId $uuid,
        OrganizationId $organizationId,
        string $name,
        string $slug,
        \DateTimeInterface $validUntil,
    ): self {
        $instance = new self($uuid, $organizationId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $organizationId,
            $name,
            $slug,
            $validUntil,
            true,
        ));

        return $instance;
    }

    public static function declareDisabled(
        WorkspaceId $uuid,
        OrganizationId $organizationId,
        string $name,
        string $slug,
    ): self {
        $instance = new self($uuid, $organizationId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $organizationId,
            $name,
            $slug,
            null,
            false,
        ));

        return $instance;
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->name = $event->name;
        $this->slug = $event->slug;
        $this->validUntil = $event->validUntil;
        $this->enabled = $event->enabled;
    }

    public function enable(?\DateTimeInterface $validUntil = null): void
    {
        if ($this->deleted) {
            throw new InvalidWorkspaceStateException('Cannot enable an already deleted Workspace.');
        }
        if ($this->enabled) {
            throw new InvalidWorkspaceStateException('Cannot enable an already enabled Workspace.');
        }

        $this->recordThat(new EnabledEvent($this->uuid, $this->organizationId, $this->version + 1, $validUntil));
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $this->enabled = true;
    }

    public function disable(?\DateTimeInterface $validUntil = null): void
    {
        if ($this->deleted) {
            throw new InvalidWorkspaceStateException('Cannot disable an already deleted Workspace.');
        }
        if (!$this->enabled) {
            throw new InvalidWorkspaceStateException('Cannot disable an already disabled Workspace.');
        }

        $this->recordThat(new DisabledEvent($this->uuid, $this->organizationId, $this->version + 1, $validUntil));
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $this->enabled = false;
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw new InvalidWorkspaceStateException('Cannot delete an already deleted Workspace.');
        }

        $this->recordThat(new DeletedEvent($this->uuid, $this->organizationId, $this->version + 1));
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $this->enabled = false;
    }
}
