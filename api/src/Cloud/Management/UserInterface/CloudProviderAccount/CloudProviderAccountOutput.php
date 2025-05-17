<?php

declare(strict_types=1);

namespace App\Cloud\Management\UserInterface\CloudProviderAccount;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderTypes;

final readonly class CloudProviderAccountOutput
{
    /**
     * @param non-empty-string   $name
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public function __construct(
        public CloudProviderAccountId $uuid,
        public CloudProviderTypes $type,
        public string $name,
        public string $description,
        public array $featureRolloutIds,
    ) {
    }
}
