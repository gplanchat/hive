<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetOneFeatureRolloutHandler
{
    public function __construct(
        private FeatureRolloutRepositoryInterface $featureRolloutRepository,
    ) {}

    public function __invoke(GetOneFeatureRollout $query): FeatureRollout
    {
        return $this->featureRolloutRepository->get($query->uuid);
    }
}
