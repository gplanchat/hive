<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final class QueryOneFeatureRolloutHandler
{
    public function __construct(
        private FeatureRolloutRepositoryInterface $featureRolloutRepository,
    ) {}

    public function __invoke(QueryOneFeatureRollout $query): FeatureRollout
    {
        return $this->featureRolloutRepository->get($query->uuid);
    }
}
