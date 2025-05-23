<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command\UseCases;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;

final readonly class CreateEnabledOrganization
{
    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public function __construct(
        public OrganizationId $uuid,
        public string $name,
        public string $slug,
        public \DateTimeInterface $validUntil,
        public array $featureRolloutIds = [],
    ) {
    }
}
