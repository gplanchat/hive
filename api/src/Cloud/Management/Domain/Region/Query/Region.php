<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\Region\Query;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Cloud\Management\Domain\Datacenter\DatacenterId;
use App\Cloud\Management\Domain\Region\RegionId;

final readonly class Region
{
    /**
     * @param DatacenterId[]     $datacenters
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public function __construct(
        public RegionId $uuid,
        public string $code,
        public string $name,
        public array $datacenters,
        public array $featureRolloutIds,
    ) {
    }
}
