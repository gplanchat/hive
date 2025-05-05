<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOrganizationInput
{
    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public function __construct(
        #[ApiProperty(
            description: 'Name of the Organization',
            schema: ['type' => 'string'],
        )]
        #[Assert\NotBlank]
        #[Assert\Length(min: 5, max: 255)]
        public string $name,
        #[ApiProperty(
            description: 'URL slug of the Organization, will be used as the authentication realm',
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
            description: 'Identifiers of the feature rollouts',
            schema: ['type' => 'list', 'items' => ['type' => 'string', 'pattern' => FeatureRolloutId::URI_REQUIREMENT]],
        )]
        #[Assert\All([
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Type(FeatureRolloutId::class),
            ],
        ])]
        #[Context(['iri_only' => true])]
        public array $featureRolloutIds = [],
        #[ApiProperty(
            description: 'Wether the Organization should be enabled or not',
            schema: ['type' => 'boolean', 'default' => false],
        )]
        public bool $enabled = false,
    ) {
    }
}
