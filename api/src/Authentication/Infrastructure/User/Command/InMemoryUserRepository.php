<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command;

use App\Authentication\Domain\ConflictException;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Command\DeclaredEvent;
use App\Authentication\Domain\User\Command\DeletedEvent;
use App\Authentication\Domain\User\Command\DisabledEvent;
use App\Authentication\Domain\User\Command\EnabledEvent;
use App\Authentication\Domain\User\Command\User;
use App\Authentication\Domain\User\Command\UserRepositoryInterface;
use App\Authentication\Domain\User\Query\User as QueryUser;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\User\DataFixtures\UserFixtures;
use App\Platform\Infrastructure\EventBusInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class InMemoryUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
        private EventBusInterface $eventBus,
    ) {
    }

    public function get(UserId $userId, RealmId $realmId): User
    {
        $item = $this->storage->getItem(UserFixtures::buildCacheKey($userId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof QueryUser) {
            throw new NotFoundException();
        }

        return new User(
            uuid: $value->uuid,
            realmId: $value->realmId,
            organizationId: $value->organizationId,
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
        $methodName = 'apply'.substr($event::class, strrpos($event::class, '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->storage->get(UserFixtures::buildCacheKey($event->uuid, $event->realmId), function (ItemInterface $item) use ($event): QueryUser {
            if ($item->isHit()) {
                throw new ConflictException();
            }

            $item->tag([UserFixtures::TAG]);

            return new QueryUser(
                uuid: $event->uuid,
                realmId: $event->realmId,
                authorization: $event->authorization,
                organizationId: $event->organizationId,
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
        $item = $this->storage->getItem(UserFixtures::buildCacheKey($event->uuid, $event->realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryUser) {
            throw new NotFoundException();
        }

        $item->set(new QueryUser(
            uuid: $event->uuid,
            realmId: $current->realmId,
            authorization: $current->authorization,
            organizationId: $current->organizationId,
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
        $item = $this->storage->getItem(UserFixtures::buildCacheKey($event->uuid, $event->realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryUser) {
            throw new NotFoundException();
        }

        $item->set(new QueryUser(
            uuid: $event->uuid,
            realmId: $current->realmId,
            authorization: $current->authorization,
            organizationId: $current->organizationId,
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
        if (!$this->storage->deleteItem(UserFixtures::buildCacheKey($event->uuid, $event->realmId))) {
            throw new NotFoundException();
        }
    }
}
