<?php

namespace App\Authentication\Domain\FeatureRollout;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\FeatureRollout\UseCases\FeatureRolloutPage;

interface FeatureRolloutRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(FeatureRolloutId $featureRolloutId): FeatureRollout;
    public function list(int $currentPage = 1, int $pageSize = 25): FeatureRolloutPage;
}
