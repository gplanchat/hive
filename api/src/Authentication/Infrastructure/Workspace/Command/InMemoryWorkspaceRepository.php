<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace\Command;

use App\Authentication\Domain\ConflictException;
use App\Authentication\Domain\EventBusInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Workspace\Command\DeclaredEvent;
use App\Authentication\Domain\Workspace\Command\DeletedEvent;
use App\Authentication\Domain\Workspace\Command\DisabledEvent;
use App\Authentication\Domain\Workspace\Command\EnabledEvent;
use App\Authentication\Domain\Workspace\Command\Workspace;
use App\Authentication\Domain\Workspace\Command\WorkspaceRepositoryInterface;
use App\Authentication\Domain\Workspace\Query\Workspace as QueryWorkspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\StorageMock;
use App\Authentication\Infrastructure\Workspace\DataFixtures\WorkspaceFixtures;
use Symfony\Contracts\Cache\ItemInterface;

final class InMemoryWorkspaceRepository implements WorkspaceRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
        private EventBusInterface $eventBus,
    ) {
    }

    public function get(WorkspaceId $workspaceId, RealmId $realmId): Workspace
    {
        $item = $this->storage->getItem(WorkspaceFixtures::buildCacheKey($workspaceId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof QueryWorkspace) {
            throw new NotFoundException();
        }

        return new Workspace(
            uuid: $value->uuid,
            realmId: $value->realmId,
            organizationId: $value->organizationId,
            name: $value->name,
            slug: $value->slug,
            validUntil: $value->validUntil,
            enabled: $value->enabled,
        );
    }

    public function save(Workspace $workspace): void
    {
        foreach ($events = $workspace->releaseEvents() as $event) {
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
        $this->storage->get(WorkspaceFixtures::buildCacheKey($event->uuid, $event->realmId), function (ItemInterface $item) use ($event): QueryWorkspace {
            if ($item->isHit()) {
                throw new ConflictException();
            }

            $item->tag([WorkspaceFixtures::TAG]);

            return new QueryWorkspace(
                uuid: $event->uuid,
                realmId: $event->realmId,
                organizationId: $event->organizationId,
                name: $event->name,
                slug: $event->slug,
                validUntil: $event->validUntil,
                enabled: $event->enabled,
            );
        });
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $item = $this->storage->getItem(WorkspaceFixtures::buildCacheKey($event->uuid, $event->realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryWorkspace) {
            throw new NotFoundException();
        }

        $item->set(new QueryWorkspace(
            uuid: $current->uuid,
            realmId: $current->realmId,
            organizationId: $current->organizationId,
            name: $current->name,
            slug: $current->slug,
            validUntil: $current->validUntil,
            enabled: true,
        ));

        $this->storage->save($item);
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $item = $this->storage->getItem(WorkspaceFixtures::buildCacheKey($event->uuid, $event->realmId));

            if (!$item->isHit()) {
                throw new NotFoundException();
            }

            $current = $item->get();
            if (!$current instanceof QueryWorkspace) {
                throw new NotFoundException();
            }

            $item->set(new QueryWorkspace(
                uuid: $current->uuid,
                realmId: $current->realmId,
                organizationId: $current->organizationId,
                name: $current->name,
                slug: $current->slug,
                validUntil: $current->validUntil,
                enabled: false,
            ));

            $this->storage->save($item);
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        if (!$this->storage->deleteItem(WorkspaceFixtures::buildCacheKey($event->uuid, $event->realmId))) {
            throw new NotFoundException();
        }
    }
}
