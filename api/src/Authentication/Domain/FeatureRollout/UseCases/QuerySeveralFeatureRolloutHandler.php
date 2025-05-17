<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final class QuerySeveralFeatureRolloutHandler
{
    public function __construct(
        private FeatureRolloutRepositoryInterface $featureRolloutRepository,
    ) {
    }

    public function __invoke(QuerySeveralFeatureRollout $query): FeatureRolloutPage
    {
        return $this->featureRolloutRepository->list($query->currentPage, $query->itemsPerPage);
    }
}
