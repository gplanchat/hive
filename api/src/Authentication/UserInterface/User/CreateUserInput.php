<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateUserInput
{
    /**
     * @param WorkspaceId[] $workspaceIds
     * @param RoleId[]      $roleIds
     */
    public function __construct(
        #[ApiProperty(
            description: 'Organization in which the User is assigned',
            schema: ['type' => 'string', 'pattern' => OrganizationId::URI_REQUIREMENT],
        )]
        #[Assert\NotBlank()]
        #[Context(['iri_only' => true])]
        public OrganizationId $organizationId,
        #[ApiProperty(
            description: 'User\'s display name',
            schema: ['type' => 'string'],
        )]
        #[Assert\Length(min: 3, max: 255)]
        #[Assert\Regex('/[A-Za-z0-9]+(?:[-.][A-Za-z0-9]+)*/')]
        #[Assert\NotBlank()]
        public string $username,
        #[ApiProperty(
            description: 'List of workspaces in which the User has access',
            schema: ['type' => 'array', 'items' => ['type' => 'string', 'pattern' => WorkspaceId::URI_REQUIREMENT]],
        )]
        #[Assert\All(constraints: [
            new Assert\NotBlank(),
            new Assert\Type(WorkspaceId::class),
        ])]
        #[Context(['iri_only' => true])]
        public array $workspaceIds = [],
        #[ApiProperty(
            description: 'List of roles assigned to the User',
            schema: ['type' => 'array', 'items' => ['type' => 'string', 'pattern' => RoleId::URI_REQUIREMENT]],
        )]
        #[Assert\All(constraints: [
            new Assert\NotBlank(),
            new Assert\Type(RoleId::class),
        ])]
        #[Context(['iri_only' => true])]
        public array $roleIds = [],
        #[ApiProperty(
            description: 'User\'s first name',
            schema: ['type' => 'string'],
        )]
        #[Assert\Length(min: 1, max: 255)]
        public ?string $firstName = null,
        #[ApiProperty(
            description: 'User\'s last name',
            schema: ['type' => 'string'],
        )]
        #[Assert\Length(min: 1, max: 255)]
        public ?string $lastName = null,
        #[ApiProperty(
            description: 'User\'s email',
            schema: ['type' => 'string', 'format' => 'email'],
        )]
        #[Assert\Email()]
        public ?string $email = null,
        #[ApiProperty(
            description: 'Wether the User account should be enabled or not',
            schema: ['type' => 'boolean', 'default' => false],
        )]
        public bool $enabled = false,
    ) {
    }
}
