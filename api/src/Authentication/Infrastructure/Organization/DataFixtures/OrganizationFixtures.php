<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\DataFixtures;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Infrastructure\StorageMock;
use Psr\Clock\ClockInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class OrganizationFixtures
{
    const TAG = 'tests.data-fixtures.organization';

    public function __construct(
        private ClockInterface $clock,
        private StorageMock $storage,
    ) {
    }

    public function load(): void
    {
        $this->storage->get('tests.data-fixtures.organization.01966c5a-10ef-7315-94f2-cbeec2f167d8', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Organization(
                OrganizationId::fromString('01966c5a-10ef-7315-94f2-cbeec2f167d8'),
                realmId: RealmId::fromString('acme-inc'),
                name: 'Gyroscops',
                slug: 'gyroscops',
                validUntil: $this->clock->now()->add(new \DateInterval('P3M2D')),
                featureRolloutIds: [
                    FeatureRolloutId::fromString('role.principal-administrator'),
                    FeatureRolloutId::fromString('subscription.enterprise'),
                    FeatureRolloutId::fromString('demo.lorem-ipsum'),
                    FeatureRolloutId::fromString('demo.dolor-sit-amet'),
                    FeatureRolloutId::fromString('demo.consecutir-sid'),
                ],
                enabled: true,
            );
        });
        $this->storage->get('tests.data-fixtures.organization.01966c5a-10ef-77a1-b158-d4356966e1ab', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Organization(
                OrganizationId::fromString('01966c5a-10ef-77a1-b158-d4356966e1ab'),
                realmId: RealmId::fromString('acme-inc'),
                name: 'ACME Inc.',
                slug: 'acme-inc',
                validUntil: $this->clock->now()->add(new \DateInterval('P1M24D')),
                featureRolloutIds: [
                    FeatureRolloutId::fromString('subscription.enterprise'),
                    FeatureRolloutId::fromString('demo.lorem-ipsum'),
                    FeatureRolloutId::fromString('demo.dolor-sit-amet'),
                    FeatureRolloutId::fromString('demo.consecutir-sid'),
                ],
                enabled: true,
            );
        });
        $this->storage->get('tests.data-fixtures.organization.01966c5a-10ef-76f6-9513-e3b858c22f0a', function (ItemInterface $item) {
            $item->tag([self::TAG]);

            return new Organization(
                OrganizationId::fromString('01966c5a-10ef-76f6-9513-e3b858c22f0a'),
                realmId: RealmId::fromString('acme-inc'),
                name: 'Big Corp.',
                slug: 'big-corp',
                validUntil: null,
                featureRolloutIds: [
                    FeatureRolloutId::fromString('subscription.enterprise'),
                    FeatureRolloutId::fromString('demo.lorem-ipsum'),
                    FeatureRolloutId::fromString('demo.dolor-sit-amet'),
                ],
                enabled: false,
            );
        });
    }

    public function unload(): void
    {
        $this->storage->invalidateTags([self::TAG]);
    }
}
