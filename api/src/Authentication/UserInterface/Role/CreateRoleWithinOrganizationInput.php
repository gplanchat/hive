<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\Role\ActionInterface;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateRoleWithinOrganizationInput
{
    /**
     * @param ResourceAccess[] $resourceAccesses
     */
    public function __construct(
        #[ApiProperty(
            description: 'Role\'s display name',
            schema: ['type' => 'string'],
        )]
        #[Assert\Length(min: 3, max: 255)]
        #[Assert\Regex('/[A-Za-z0-9]+(?:[-.][A-Za-z0-9]+)*/')]
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
                            'pattern' => ResourceInterface::REQUIREMENT,
                        ],
                        'actions' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                                'pattern' => ActionInterface::REQUIREMENT,
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
