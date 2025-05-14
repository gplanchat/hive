<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;

final readonly class AddFeatureRolloutsToOrganizationInput
{
    /** @param FeatureRolloutId[] $featureRolloutIds */
    public function __construct(
        #[ApiProperty(
            description: 'Feature Rollouts to be added to the Organization',
            schema: ['type' => 'array', 'items' => ['type' => 'string', 'format' => FeatureRolloutId::URI_REQUIREMENT]],
        )]
        public array $featureRolloutIds,
    ) {
    }
}
