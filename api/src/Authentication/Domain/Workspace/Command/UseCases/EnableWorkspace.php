<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class EnableWorkspace
{
    public function __construct(
        public WorkspaceId $uuid,
        public RealmId $realmId,
        public ?\DateTimeInterface $validUntil = null,
    ) {
    }
}
