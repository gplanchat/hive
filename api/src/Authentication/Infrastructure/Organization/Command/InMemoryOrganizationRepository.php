<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command;

use App\Authentication\Domain\ConflictException;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\DeclaredEvent;
use App\Authentication\Domain\Organization\Command\DeletedEvent;
use App\Authentication\Domain\Organization\Command\DisabledEvent;
use App\Authentication\Domain\Organization\Command\EnabledEvent;
use App\Authentication\Domain\Organization\Command\Organization;
use App\Authentication\Domain\Organization\Command\OrganizationRepositoryInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization as QueryOrganization;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use App\Authentication\Infrastructure\StorageMock;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class InMemoryOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        private StorageMock $storage,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function get(OrganizationId $organizationId): Organization
    {
        $item = $this->storage->getItem("tests.data-fixtures.organization.{$organizationId->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $value = $item->get();
        if (!$value instanceof QueryOrganization) {
            throw new NotFoundException();
        }

        return new Organization(
            uuid: $value->uuid,
            name: $value->name,
            slug: $value->slug,
            validUntil: $value->validUntil,
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
        $this->storage->get("tests.data-fixtures.organization.{$event->uuid->toString()}", function (ItemInterface $item) use ($event): QueryOrganization {
            if ($item->isHit()) {
                throw new ConflictException();
            }

            $item->tag([OrganizationFixtures::TAG]);

            return new QueryOrganization(
                uuid: $event->uuid,
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
        $item = $this->storage->getItem("tests.data-fixtures.organization.{$event->uuid->toString()}");

        if (!$item->isHit()) {
            throw new NotFoundException();
        }

        $current = $item->get();
        if (!$current instanceof QueryOrganization) {
            throw new NotFoundException();
        }

        $item->set(new QueryOrganization(
            uuid: $current->uuid,
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
        $item = $this->storage->getItem("tests.data-fixtures.organization.{$event->uuid->toString()}");

            if (!$item->isHit()) {
                throw new NotFoundException();
            }

            $current = $item->get();
            if (!$current instanceof QueryOrganization) {
                throw new NotFoundException();
            }

            $item->set(new QueryOrganization(
                uuid: $current->uuid,
                name: $current->name,
                slug: $current->slug,
                validUntil: $event->validUntil,
                featureRolloutIds: $current->featureRolloutIds,
                enabled: false,
            ));

            $this->storage->save($item);
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        if (!$this->storage->deleteItem("tests.data-fixtures.organization.{$event->uuid->toString()}")) {
            throw new NotFoundException();
        }
    }
}
