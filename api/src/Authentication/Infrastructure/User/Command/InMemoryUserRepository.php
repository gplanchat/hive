<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command;

use App\Authentication\Domain\EventBusInterface;
use App\Authentication\Domain\ConflictException;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\User\Command\DeclaredEvent;
use App\Authentication\Domain\User\Command\DeletedEvent;
use App\Authentication\Domain\User\Command\DisabledEvent;
use App\Authentication\Domain\User\Command\EnabledEvent;
use App\Authentication\Domain\User\Command\User;
use App\Authentication\Domain\User\Command\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\User\Query\User as QueryUser;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;
use App\Authentication\Infrastructure\StorageMock;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class InMemoryUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
        private EventBusInterface $eventBus,
    ) {
    }

    public function get(UserId $userId): User
    {
        $item = $this->storage->getItem("tests.data-fixtures.user.{$userId->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof QueryUser) {
            throw new NotFoundException();
        }

        return new User(
            uuid: $value->uuid,
            organizationId: $value->organizationId,
            workspaceIds: $value->workspaceIds,
            roleIds: $value->roleIds,
            username: $value->username,
            firstName: $value->firstName,
            lastName: $value->lastName,
            email: $value->email,
            enabled: $value->enabled,
        );
    }

    public function save(User $user): void
    {
        foreach ($events = $user->releaseEvents() as $event) {
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
        $methodName = 'apply'.substr(get_class($event), strrpos(get_class($event), '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->storage->get("tests.data-fixtures.user.{$event->uuid->toString()}", function (ItemInterface $item) use ($event): QueryUser {
            if ($item->isHit()) {
                throw new ConflictException();
            }

            $item->tag([UserFixtures::TAG]);

            return new QueryUser(
                uuid: $event->uuid,
                organizationId: $event->organizationId,
                realmId: $event->realmId,
                workspaceIds: $event->workspaceIds,
                roleIds: $event->roleIds,
                username: $event->username,
                firstName: $event->firstName,
                lastName: $event->lastName,
                email: $event->email,
                enabled: $event->enabled,
            );
        });
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $item = $this->storage->getItem("tests.data-fixtures.user.{$event->uuid->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryUser) {
            throw new NotFoundException();
        }

        $item->set(new QueryUser(
            uuid: $event->uuid,
            organizationId: $current->organizationId,
            realmId: $current->realmId,
            workspaceIds: $current->workspaceIds,
            roleIds: $current->roleIds,
            username: $current->username,
            firstName: $current->firstName,
            lastName: $current->lastName,
            email: $current->email,
            enabled: true,
        ));

        $this->storage->save($item);
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $item = $this->storage->getItem("tests.data-fixtures.user.{$event->uuid->toString()}");

            if (!$item->isHit()) {
                throw new NotFoundException();
            }

            $current = $item->get();
            if (!$current instanceof QueryUser) {
                throw new NotFoundException();
            }

            $item->set(new QueryUser(
                uuid: $event->uuid,
                organizationId: $current->organizationId,
                realmId: $current->realmId,
                workspaceIds: $current->workspaceIds,
                roleIds: $current->roleIds,
                username: $current->username,
                firstName: $current->firstName,
                lastName: $current->lastName,
                email: $current->email,
                enabled: false,
            ));

            $this->storage->save($item);
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        if (!$this->storage->deleteItem("tests.data-fixtures.user.{$event->uuid->toString()}")) {
            throw new NotFoundException();
        }
    }
}
