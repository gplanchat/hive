<?php

namespace App\Authentication\Domain\Workspace\Command;

use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Workspace\WorkspaceId;

interface WorkspaceRepositoryInterface
{
    public function get(
        WorkspaceId $workspaceId,
        RealmId $realmId,
    ): Workspace;

    public function save(
        Workspace $workspace,
    ): void;
}
