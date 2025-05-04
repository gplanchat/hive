<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Command;

use App\Authentication\Domain\ConflictException;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Role\Command\DeclaredEvent;
use App\Authentication\Domain\Role\Command\DeletedEvent;
use App\Authentication\Domain\Role\Command\Role;
use App\Authentication\Domain\Role\Command\RoleRepositoryInterface;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\Role\Query\Role as QueryRole;
use App\Authentication\Infrastructure\Role\DataFixtures\RoleFixtures;
use App\Authentication\Infrastructure\StorageMock;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class InMemoryRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function get(RoleId $roleId): Role
    {
        $item = $this->storage->getItem("tests.data-fixtures.role.{$roleId->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof QueryRole) {
            throw new NotFoundException();
        }

        return new Role(
            uuid: $value->uuid,
            organizationId: $value->organizationId,
            identifier: $value->identifier,
            label: $value->label,
            resourceAccesses: $value->resourceAccesses,
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
            $this->messageBus->dispatch($event);
        }
    }

    private function saveEvent(object $event): void
    {
        $methodName = 'apply'.substr(get_class($event), strrpos(get_class($event), '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->storage->get("tests.data-fixtures.role.{$event->uuid->toString()}", function (ItemInterface $item) use ($event): QueryRole {
            if ($item->isHit()) {
                throw new ConflictException();
            }

            $item->tag([RoleFixtures::TAG]);

            return new QueryRole(
                uuid: $event->uuid,
                organizationId: $event->organizationId,
                identifier: $event->identifier,
                label: $event->label,
                resourceAccesses: $event->resourceAccesses,
            );
        });
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        if (!$this->storage->deleteItem("tests.data-fixtures.role.{$event->uuid->toString()}")) {
            throw new NotFoundException();
        }
    }
}
