<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\Datacenter\Query;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;
use App\Cloud\Management\Domain\Datacenter\DatacenterId;

final readonly class Datacenter
{
    /**
     * @param non-empty-string              $code
     * @param non-empty-string              $name
     * @param FeatureRolloutId[]            $featureRolloutIds
     * @param PlatformCapabilityInterface[] $platformCapabilities
     */
    public function __construct(
        public DatacenterId $uuid,
        public string $code,
        public string $name,
        public CloudProviderAccountId $cloudProviderId,
        public DatacenterAddress $address,
        public array $featureRolloutIds,
        public array $platformCapabilities,
    ) {
    }
}
