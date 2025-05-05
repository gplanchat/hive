<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace\DataFixtures;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\StorageMock;
use Psr\Clock\ClockInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class WorkspaceFixtures
{
    const TAG = 'tests.data-fixtures.workspace';

    public function __construct(
        private ClockInterface $clock,
        private StorageMock $storage,
    ) {
    }

    public function load(): void
    {
        $this->storage->get('tests.data-fixtures.workspace.01966c5a-10ef-723c-bc33-2b1dc30d8963', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-723c-bc33-2b1dc30d8963'),
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
                'Lorem ipsum',
                'lorem-ipsum',
                validUntil: $this->clock->now()->add(new \DateInterval('P3M2D')),
                enabled: true,
            );
        });
        $this->storage->get('tests.data-fixtures.workspace.01966cc2-0323-7a38-9da3-3aeea904ea49', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Workspace(
                WorkspaceId::fromString('01966cc2-0323-7a38-9da3-3aeea904ea49'),
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
                'Dolor sit amet',
                'dolor-sit-amet',
                validUntil: $this->clock->now()->add(new \DateInterval('P3M2D')),
                enabled: true,
            );
        });
        $this->storage->get('tests.data-fixtures.workspace.01966c5a-10ef-7328-8638-39bf546a5bf4', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-7328-8638-39bf546a5bf4'),
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
                'Consectetur adipiscing elit',
                'consectetur-adipiscing-elit',
                validUntil: $this->clock->now()->add(new \DateInterval('P3M2D')),
                enabled: true,
            );
        });
        $this->storage->get('tests.data-fixtures.workspace.01966c5a-10ef-7f9c-8c9f-80657a996b9d', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-7f9c-8c9f-80657a996b9d'),
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
                'Elit adipiscing',
                'elit-adipiscing',
                validUntil: $this->clock->now()->add(new \DateInterval('P3M2D')),
                enabled: true,
            );
        });
        $this->storage->get('tests.data-fixtures.workspace.01966c5a-10ef-70ce-ab8c-c455e874c3fc', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-70ce-ab8c-c455e874c3fc'),
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
                'Adipiscing elit adipiscing',
                'adipiscing-elit-adipiscing',
                validUntil: $this->clock->now()->add(new \DateInterval('P3M2D')),
                enabled: true,
            );
        });
        $this->storage->get('tests.data-fixtures.workspace.01966c5a-10ef-7795-9e13-7359dd58b49c', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Workspace(
                WorkspaceId::fromString('01966c5a-10ef-7795-9e13-7359dd58b49c'),
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
                'Consectetur adipiscing elit',
                'consectetur-adipiscing-elit',
                validUntil: $this->clock->now()->add(new \DateInterval('P3M2D')),
                enabled: false,
            );
        });
    }

    public function unload(): void
    {
        $this->storage->invalidateTags([self::TAG]);
    }
}
