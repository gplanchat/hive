<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class QuerySeveralUserInWorkspace
{
    public function __construct(
        public WorkspaceId $workspaceId,
        public int $currentPage = 1,
        public int $itemsPerPage = 25,
    ) {
    }
}
