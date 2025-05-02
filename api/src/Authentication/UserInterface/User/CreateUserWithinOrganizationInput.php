<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Symfony\Component\Serializer\Attribute\Context;

final readonly class CreateUserWithinOrganizationInput
{
    /**
     * @param WorkspaceId[] $workspaceIds
     * @param RoleId[] $roleIds
     */
    public function __construct(
        #[ApiProperty(
            description: 'List of workspaces in which the user has access',
            schema: ['type' => 'array', 'items' => ['type' => 'string', 'pattern' => WorkspaceId::REQUIREMENT]],
        )]
        #[Context(['iri_only' => true])]
        public array $workspaceIds = [],
        #[ApiProperty(
            description: 'List of roles assigned to the user',
            schema: ['type' => 'array', 'items' => ['type' => 'string', 'pattern' => RoleId::REQUIREMENT]],
        )]
        #[Context(['iri_only' => true])]
        public array $roleIds = [],
        #[ApiProperty(
            description: 'User\'s display name',
            schema: ['type' => 'string'],
        )]
        public ?string $identifier = null,
        #[ApiProperty(
            description: 'User\'s first name',
            schema: ['type' => 'string'],
        )]
        public ?string $firstName = null,
        #[ApiProperty(
            description: 'User\'s last name',
            schema: ['type' => 'string'],
        )]
        public ?string $lastName = null,
        #[ApiProperty(
            description: 'User\'s email',
            schema: ['type' => 'string', 'format' => 'email'],
        )]
        public ?string $email = null,
        #[ApiProperty(
            description: 'Wether the User account should be enabled or not',
            schema: ['type' => 'boolean', 'default' => false],
        )]
        public bool $enabled = false,
    ) {
    }
}
