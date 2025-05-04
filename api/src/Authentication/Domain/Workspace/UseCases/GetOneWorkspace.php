<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\UseCases;

use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class GetOneWorkspace
{
    public function __construct(
        public WorkspaceId $uuid,
    ) {
    }
}
