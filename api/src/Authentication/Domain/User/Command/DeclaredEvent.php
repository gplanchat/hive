<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\User\UserId;

final readonly class DeclaredEvent
{
    public function __construct(
        public UserId $uuid,
        public int $version,
        public OrganizationId $organizationId,
        public array $workspaceIds,
        public array $roleIds,
        public string $username,
        public string $firstName,
        public string $lastName,
        public string $email,
        public bool $enabled = true,
    ) {
    }
}
