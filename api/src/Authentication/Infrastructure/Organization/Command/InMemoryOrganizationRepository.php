<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command;

use App\Authentication\Domain\ConflictException;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\AddedFeatureRolloutsEvent;
use App\Authentication\Domain\Organization\Command\DeclaredEvent;
use App\Authentication\Domain\Organization\Command\DeletedEvent;
use App\Authentication\Domain\Organization\Command\DisabledEvent;
use App\Authentication\Domain\Organization\Command\EnabledEvent;
use App\Authentication\Domain\Organization\Command\Organization;
use App\Authentication\Domain\Organization\Command\OrganizationRepositoryInterface;
use App\Authentication\Domain\Organization\Command\RemovedFeatureRolloutsEvent;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization as QueryOrganization;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\StorageMock;
use App\Platform\Infrastructure\Collection\Collection;
use App\Platform\Infrastructure\EventBusInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class InMemoryOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
        private EventBusInterface $eventBus,
    ) {
    }

    public function get(OrganizationId $organizationId, RealmId $realmId): Organization
    {
        $item = $this->storage->getItem(OrganizationFixtures::buildCacheKey($organizationId, $realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof QueryOrganization) {
            throw new NotFoundException();
        }

        return new Organization(
            uuid: $value->uuid,
            realmId: $value->realmId,
            featureRolloutIds: $value->featureRolloutIds,
            enabled: $value->enabled,
        );
    }

    public function save(Organization $organization): void
    {
        foreach ($events = $organization->releaseEvents() as $event) {
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
        $this->storage->get(OrganizationFixtures::buildCacheKey($event->uuid, $event->realmId), function (ItemInterface $item) use ($event): QueryOrganization {
            if ($item->isHit()) {
                throw new ConflictException();
            }

            $item->tag([OrganizationFixtures::TAG]);

            return new QueryOrganization(
                uuid: $event->uuid,
                realmId: $event->realmId,
                name: $event->name,
                slug: $event->slug,
                validUntil: $event->validUntil,
                featureRolloutIds: $event->featureRolloutIds,
                enabled: $event->enabled,
            );
        });
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $item = $this->storage->getItem(OrganizationFixtures::buildCacheKey($event->uuid, $event->realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryOrganization) {
            throw new NotFoundException();
        }

        $item->set(new QueryOrganization(
            uuid: $current->uuid,
            realmId: $current->realmId,
            name: $current->name,
            slug: $current->slug,
            validUntil: $event->validUntil,
            featureRolloutIds: $current->featureRolloutIds,
            enabled: true,
        ));

        $this->storage->save($item);
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $item = $this->storage->getItem(OrganizationFixtures::buildCacheKey($event->uuid, $event->realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryOrganization) {
            throw new NotFoundException();
        }

        $item->set(new QueryOrganization(
            uuid: $current->uuid,
            realmId: $current->realmId,
            name: $current->name,
            slug: $current->slug,
            validUntil: $event->validUntil,
            featureRolloutIds: $current->featureRolloutIds,
            enabled: false,
        ));

        $this->storage->save($item);
    }

    private function applyAddedFeatureRolloutsEvent(AddedFeatureRolloutsEvent $event): void
    {
        $item = $this->storage->getItem(OrganizationFixtures::buildCacheKey($event->uuid, $event->realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryOrganization) {
            throw new NotFoundException();
        }

        $item->set(new QueryOrganization(
            uuid: $current->uuid,
            realmId: $current->realmId,
            name: $current->name,
            slug: $current->slug,
            validUntil: $current->validUntil,
            featureRolloutIds: Collection::fromArray([...$current->featureRolloutIds, ...$event->featureRolloutIds])
                ->unique(fn (FeatureRolloutId $left, FeatureRolloutId $right) => $left->equals($right))
                ->toArray(),
            enabled: $current->enabled,
        ));

        $this->storage->save($item);
    }

    private function applyRemovedFeatureRolloutsEvent(RemovedFeatureRolloutsEvent $event): void
    {
        $item = $this->storage->getItem(OrganizationFixtures::buildCacheKey($event->uuid, $event->realmId));

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryOrganization) {
            throw new NotFoundException();
        }

        $item->set(new QueryOrganization(
            uuid: $current->uuid,
            realmId: $current->realmId,
            name: $current->name,
            slug: $current->slug,
            validUntil: $current->validUntil,
            featureRolloutIds: Collection::fromArray($current->featureRolloutIds)
                ->filter(fn (FeatureRolloutId $current) => Collection::fromArray($event->featureRolloutIds)
                    ->none(fn (FeatureRolloutId $toRemove) => $current->equals($toRemove)))
                ->toArray(),
            enabled: $current->enabled,
        ));

        $this->storage->save($item);
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        if (!$this->storage->deleteItem(OrganizationFixtures::buildCacheKey($event->uuid, $event->realmId))) {
            throw new NotFoundException();
        }
    }
}
