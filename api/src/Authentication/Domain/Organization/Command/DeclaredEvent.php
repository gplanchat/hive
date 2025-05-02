<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;

final readonly class DeclaredEvent
{
    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public function __construct(
        public OrganizationId $uuid,
        public int $version,
        public string $name,
        public string $slug,
        public ?\DateTimeInterface $validUntil = null,
        public array $featureRolloutIds = [],
        public bool $enabled = true,
    ) {
    }
}
