<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Command\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class CreatePendingWorkspace
{
    public function __construct(
        public WorkspaceId $uuid,
        public OrganizationId $organizationId,
        public string $name,
        public string $slug,
    ) {
    }
}
