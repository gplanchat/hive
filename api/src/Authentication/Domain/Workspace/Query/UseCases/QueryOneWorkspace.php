<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query\UseCases;

use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class QueryOneWorkspace
{
    public function __construct(
        public WorkspaceId $uuid,
    ) {
    }
}
