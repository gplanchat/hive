<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class DeletedEvent
{
    public function __construct(
        public WorkspaceId $uuid,
        public OrganizationId $organizationId,
        public int $version,
    ) {
    }
}
