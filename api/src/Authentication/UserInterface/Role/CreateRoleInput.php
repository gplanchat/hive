<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\ResourceInterface;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateRoleInput
{
    /**
     * @param ResourceAccess[] $resourceAccesses
     */
    public function __construct(
        #[ApiProperty(
            description: 'Organization in which the user is assigned',
            schema: ['type' => 'string', 'pattern' => OrganizationId::REQUIREMENT],
        )]
        #[Context(['iri_only' => true])]
        #[Assert\NotBlank()]
        public OrganizationId $organizationId,
        #[ApiProperty(
            description: 'Role\'s display name',
            schema: ['type' => 'string'],
        )]
        #[Assert\Regex('/[A-Za-z0-9]+(?:[-.][A-Za-z0-9]+)*/')]
        #[Assert\Length(min: 3, max: 255)]
        public ?string $identifier = null,
        #[ApiProperty(
            description: 'Role\'s first name',
            schema: ['type' => 'string'],
        )]
        #[Assert\Length(min: 1, max: 255)]
        public ?string $label = null,
        #[ApiProperty(
            description: 'List of resource authorizations',
            schema: [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'resource' => [
                            'type' => 'string',
                            'pattern' => Requirement::ASCII_SLUG,
                        ],
                        'actions' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'pattern' => Requirement::ASCII_SLUG,
                            ],
                        ],
                    ],
                ],
            ],
        )]
        #[Assert\All(constraints: [
            new Assert\NotBlank(),
            new Assert\Type(ResourceAccess::class),
        ])]
        public array $resourceAccesses = [],
    ) {
    }
}
