<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Command;

use App\Authentication\Domain\ConflictException;
use App\Authentication\Domain\EventBusInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Command\DeclaredEvent;
use App\Authentication\Domain\Role\Command\DeletedEvent;
use App\Authentication\Domain\Role\Command\Role;
use App\Authentication\Domain\Role\Command\RoleRepositoryInterface;
use App\Authentication\Domain\Role\Query\Role as QueryRole;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class InMemoryRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
        private EventBusInterface $eventBus,
    ) {
    }

    public function get(RoleId $roleId, RealmId $realmId): Role
    {
        $item = $this->storage->getItem(RoleFixtures::buildCacheKey($roleId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof QueryRole) {
            throw new NotFoundException();
        }

        return new Role(
            uuid: $value->uuid,
            realmId: $value->realmId,
            organizationId: $value->organizationId,
        );
    }

    public function save(Role $role): void
    {
        foreach ($events = $role->releaseEvents() as $event) {
            try {
                $this->saveEvent($event);
            } catch (\Throwable $exception) {
                throw $exception;
            }
        }

        foreach ($events as $event) {
            $this->eventBus->emit($event);
        }
    }

    private function saveEvent(object $event): void
    {
        $methodName = 'apply'.substr($event::class, strrpos($event::class, '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->storage->get(RoleFixtures::buildCacheKey($event->uuid, $event->realmId), function (ItemInterface $item) use ($event): QueryRole {
            if ($item->isHit()) {
                throw new ConflictException();
            }

            $item->tag([RoleFixtures::TAG]);

            return new QueryRole(
                uuid: $event->uuid,
                realmId: $event->realmId,
                organizationId: $event->organizationId,
                identifier: $event->identifier,
                label: $event->label,
                resourceAccesses: $event->resourceAccesses,
            );
        });
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        if (!$this->storage->deleteItem(RoleFixtures::buildCacheKey($event->uuid, $event->realmId))) {
            throw new NotFoundException();
        }
    }
}
