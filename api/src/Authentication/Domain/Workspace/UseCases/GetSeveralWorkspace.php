<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\UseCases;

final readonly class GetSeveralWorkspace
{
    public function __construct(
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
