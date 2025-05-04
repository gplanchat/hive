<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\Role\Query\Role as QueryRole;
use App\Authentication\UserInterface\Role\CreateRoleInput;
use App\Authentication\UserInterface\Role\CreateRoleProcessor;
use App\Authentication\UserInterface\Role\CreateRoleWithinOrganizationInput;
use App\Authentication\UserInterface\Role\DeleteRoleProcessor;
use App\Authentication\UserInterface\Role\GetOneRoleProvider;

#[Post(
    uriTemplate: '/authentication/organizations/{organizationId}/roles',
    input: CreateRoleWithinOrganizationInput::class,
    output: QueryRole::class,
    processor: CreateRoleProcessor::class,
)]
#[Post(
    uriTemplate: '/authentication/roles',
    input: CreateRoleInput::class,
    output: QueryRole::class,
    processor: CreateRoleProcessor::class,
)]
#[Delete(
    uriTemplate: '/authentication/roles/{uuid}',
    uriVariables: ['uuid'],
    input: false,
    output: false,
    provider: GetOneRoleProvider::class,
    processor: DeleteRoleProcessor::class,
)]
final class Role
{
    /** @param ResourceAccess[] $resourceAccesses */
    public function __construct(
        public readonly RoleId $uuid,
        public readonly OrganizationId $organizationId,
        private ?string $identifier = null,
        private ?string $label = null,
        private array $resourceAccesses = [],
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

    public static function declare(
        RoleId $uuid,
        OrganizationId $organizationId,
        string $identifier,
        string $label,
        array $resourceAccesses = [],
    ): self {
        $instance = new self($uuid, $organizationId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $organizationId,
            $identifier,
            $label,
            $resourceAccesses,
        ));

        return $instance;
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->label = $event->label;
        $this->resourceAccesses = $event->resourceAccesses;
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw new InvalidRoleStateException('Cannot delete an already deleted Role.');
        }

        $this->recordThat(new DeletedEvent($this->uuid, $this->version + 1));
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $this->deleted = true;
    }
}
