<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class CreateEnabledUser
{
    /**
     * @param WorkspaceId[] $workspaceIds
     * @param RoleId[] $roleIds
     */
    public function __construct(
        public UserId $uuid,
        public OrganizationId $organizationId,
        public array $workspaceIds = [],
        public array $roleIds = [],
        public ?string $username = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
    ) {
    }
}
