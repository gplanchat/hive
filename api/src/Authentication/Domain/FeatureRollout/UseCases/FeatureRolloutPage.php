<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

use App\Authentication\Domain\FeatureRollout\FeatureRollout;

final readonly class FeatureRolloutPage implements \IteratorAggregate, \Countable
{
    /** @var FeatureRollout[] */
    private array $featureRollouts;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        FeatureRollout ...$featureRollouts,
    ) {
        $this->featureRollouts = $featureRollouts;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->featureRollouts;
    }

    public function count(): int
    {
        return \count($this->featureRollouts);
    }
}
