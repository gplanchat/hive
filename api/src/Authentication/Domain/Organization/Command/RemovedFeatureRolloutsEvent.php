<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;

final readonly class RemovedFeatureRolloutsEvent
{
    /** @param FeatureRolloutId[] $featureRolloutIds */
    public function __construct(
        public OrganizationId $uuid,
        public int $version,
        public RealmId $realmId,
        public array $featureRolloutIds,
    ) {
    }
}
