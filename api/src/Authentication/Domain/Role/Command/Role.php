<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\RoleId;

final class Role
{
    /**
     * @param object[] $events
     */
    public function __construct(
        public readonly RoleId $uuid,
        public readonly RealmId $realmId,
        public readonly OrganizationId $organizationId,
        private bool $deleted = false,
        private array $events = [],
        private int $version = 0,
    ) {
    }

    private function apply(object $event): void
    {
        $methodName = 'apply'.substr($event::class, strrpos($event::class, '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function recordThat(object $event): void
    {
        $this->events[] = $event;
        ++$this->version;
        $this->apply($event);
    }

    /**
     * @return object[]
     */
    public function releaseEvents(): array
    {
        $releasedEvents = $this->events;
        $this->events = [];

        return $releasedEvents;
    }

    /**
     * @param ResourceAccess[] $resourceAccesses
     */
    public static function declare(
        RoleId $uuid,
        RealmId $realmId,
        OrganizationId $organizationId,
        string $identifier,
        string $label,
        array $resourceAccesses = [],
    ): self {
        $instance = new self($uuid, $realmId, $organizationId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $realmId,
            $organizationId,
            $identifier,
            $label,
            $resourceAccesses,
        ));

        return $instance;
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw new InvalidRoleStateException('Cannot delete an already deleted Role.');
        }

        $this->recordThat(new DeletedEvent($this->uuid, $this->version + 1, $this->realmId, $this->organizationId));
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $this->deleted = true;
    }
}
