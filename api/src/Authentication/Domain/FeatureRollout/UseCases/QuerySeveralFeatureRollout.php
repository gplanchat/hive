<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

final readonly class QuerySeveralFeatureRollout
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
