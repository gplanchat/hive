<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class DisableWorkspace
{
    public function __construct(
        public WorkspaceId $uuid,
        public ?\DateTimeInterface $validUntil = null,
    ) {
    }
}
