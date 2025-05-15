<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout;

use App\Authentication\Domain\FeatureRollout\UseCases\FeatureRolloutPage;
use App\Authentication\Domain\NotFoundException;

interface FeatureRolloutRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(FeatureRolloutId $featureRolloutId): FeatureRollout;

    public function list(int $currentPage = 1, int $pageSize = 25): FeatureRolloutPage;
}
