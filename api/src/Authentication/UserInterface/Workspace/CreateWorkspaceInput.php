<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Workspace;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\Organization\OrganizationId;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateWorkspaceInput
{
    public function __construct(
        #[ApiProperty(
            description: 'Organization in which the Workspace is assigned',
            schema: ['type' => 'string', 'pattern' => OrganizationId::URI_REQUIREMENT],
        )]
        #[Context(['iri_only' => true])]
        #[Assert\NotBlank()]
        public OrganizationId $organizationId,
        #[ApiProperty(
            description: 'Name of the Workspace',
            schema: ['type' => 'string'],
        )]
        #[Assert\NotBlank]
        #[Assert\Length(min: 5, max: 255)]
        public string $name,
        #[ApiProperty(
            description: 'URL slug of the Workspace, will be used as the authentication realm',
            schema: ['type' => 'string', 'format' => Requirement::ASCII_SLUG],
        )]
        #[Assert\NotBlank]
        #[Assert\Length(min: 5, max: 255)]
        public string $slug,
        #[ApiProperty(
            description: 'End date of validity of all subscriptions',
            schema: ['type' => 'string', 'format' => 'date'],
        )]
        #[Assert\When([
            'expression' => 'this.enabled == true',
            'constraints' => [
                new Assert\NotNull(),
            ]
        ])]
        #[Context(['datetime_format' => 'Y-m-d'])]
        public ?\DateTimeInterface $validUntil = null,
        #[ApiProperty(
            description: 'Wether the Workspace should be enabled or not',
            schema: ['type' => 'boolean', 'default' => false],
        )]
        public bool $enabled = false,
    ) {
    }
}
