<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;

final readonly class GetOneFeatureRollout
{
    public function __construct(
        public FeatureRolloutId $uuid,
    ) {
    }
}
