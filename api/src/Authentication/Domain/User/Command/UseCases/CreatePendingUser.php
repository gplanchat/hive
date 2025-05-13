<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command\UseCases;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\AuthorizationInterface;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;

final readonly class CreatePendingUser
{
    /**
     * @param WorkspaceId[] $workspaceIds
     * @param RoleId[] $roleIds
     */
    public function __construct(
        public UserId $uuid,
        public RealmId $realmId,
        public AuthorizationInterface $authorization,
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
