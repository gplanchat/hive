<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

final readonly class GetSeveralUser
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
