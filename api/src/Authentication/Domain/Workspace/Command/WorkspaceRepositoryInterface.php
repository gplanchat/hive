<?php

namespace App\Authentication\Domain\Workspace\Command;

use App\Authentication\Domain\Workspace\WorkspaceId;

interface WorkspaceRepositoryInterface
{
    public function get(
        WorkspaceId $workspaceId,
    ): Workspace;

    public function save(
        Workspace $workspace,
    ): void;
}
