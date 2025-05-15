<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\UserInterface\Organization\AddFeatureRolloutsToOrganizationInput;
use App\Authentication\UserInterface\Organization\AddFeatureRolloutsToOrganizationProcessor;
use App\Authentication\UserInterface\Organization\CreateOrganizationInput;
use App\Authentication\UserInterface\Organization\CreateOrganizationProcessor;
use App\Authentication\UserInterface\Organization\DeleteOrganizationProcessor;
use App\Authentication\UserInterface\Organization\DisableOrganizationInput;
use App\Authentication\UserInterface\Organization\DisableOrganizationProcessor;
use App\Authentication\UserInterface\Organization\EnableOrganizationInput;
use App\Authentication\UserInterface\Organization\EnableOrganizationProcessor;
use App\Authentication\UserInterface\Organization\QueryOneOrganizationProvider;
use App\Authentication\UserInterface\Organization\QuerySeveralOrganizationProvider;
use App\Authentication\UserInterface\Organization\RemoveFeatureRolloutsFromOrganizationInput;
use App\Authentication\UserInterface\Organization\RemoveFeatureRolloutsFromOrganizationProcessor;
use Symfony\Component\Serializer\Attribute\Context;

#[Delete(
    uriTemplate: '/authentication/{realm}/organizations/{uuid}',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: false,
    output: false,
    provider: QueryOneOrganizationProvider::class,
    processor: DeleteOrganizationProcessor::class,
)]
#[Get(
    uriTemplate: '/authentication/{realm}/organizations/{uuid}',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the Organization',
                required: true,
                schema: ['pattern' => OrganizationId::REQUIREMENT],
            ),
            new Parameter(
                name: 'realm',
                in: 'path',
                description: 'Code of the Realm',
                required: true,
                schema: ['pattern' => RealmId::REQUIREMENT],
            ),
        ],
    ),
    security: 'is_granted("IS_AUTHENTICATED")',
    provider: QueryOneOrganizationProvider::class,
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/organizations',
    uriVariables: [
        'realm' => 'realmId',
    ],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'realm',
                in: 'path',
                description: 'Code of the Realm',
                required: true,
                schema: ['pattern' => RealmId::REQUIREMENT],
            ),
        ],
    ),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['uuid' => 'ASC'],
    security: 'is_granted("IS_AUTHENTICATED")',
    provider: QuerySeveralOrganizationProvider::class,
    itemUriTemplate: '/authentication/{realm}/organizations/{uuid}',
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/organizations/{uuid}/enable',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: EnableOrganizationInput::class,
    output: self::class,
    provider: QueryOneOrganizationProvider::class,
    processor: EnableOrganizationProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/organizations/{uuid}/disable',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: DisableOrganizationInput::class,
    output: self::class,
    provider: QueryOneOrganizationProvider::class,
    processor: DisableOrganizationProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/organizations/{uuid}/add-features',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: AddFeatureRolloutsToOrganizationInput::class,
    output: self::class,
    provider: QueryOneOrganizationProvider::class,
    processor: AddFeatureRolloutsToOrganizationProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/organizations/{uuid}/remove-features',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: RemoveFeatureRolloutsFromOrganizationInput::class,
    output: self::class,
    provider: QueryOneOrganizationProvider::class,
    processor: RemoveFeatureRolloutsFromOrganizationProcessor::class,
)]
#[Post(
    uriTemplate: '/authentication/{realm}/organizations',
    uriVariables: [
        'realm' => 'realmId',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: CreateOrganizationInput::class,
    output: self::class,
    processor: CreateOrganizationProcessor::class,
    itemUriTemplate: '/authentication/{realm}/organizations/{uuid}',
)]
final readonly class Organization
{
    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public function __construct(
        #[ApiProperty(
            description: 'Identifier of the Organization',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => OrganizationId::REQUIREMENT],
        )]
        public OrganizationId $uuid,
        #[ApiProperty(
            description: 'Realm of the Organization',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => RealmId::REQUIREMENT],
        )]
        public RealmId $realmId,
        #[ApiProperty(
            description: 'Name of the Organization',
            schema: ['type' => 'string'],
        )]
        public string $name,
        #[ApiProperty(
            description: 'Slug of the Organization',
            schema: ['type' => 'string'],
        )]
        public string $slug,
        #[ApiProperty(
            description: 'End date of validity of all subscriptions',
            schema: ['type' => 'string', 'format' => 'date', 'required' => true, 'nullable' => true],
        )]
        #[Context(['datetime_format' => 'Y-m-d', 'skip_null_values' => false])]
        public ?\DateTimeInterface $validUntil = null,
        #[ApiProperty(
            description: 'Identifiers of the feature rollouts',
            schema: ['type' => 'list', 'items' => ['type' => 'string', 'pattern' => FeatureRolloutId::URI_REQUIREMENT]],
        )]
        #[Context(['iri_only' => true])]
        public array $featureRolloutIds = [],
        #[ApiProperty(
            description: 'Wether the Organization is enabled or not',
            schema: ['type' => 'boolean'],
        )]
        public bool $enabled = true,
    ) {
        array_all($this->featureRolloutIds, fn ($featureRolloutId) => $featureRolloutId instanceof FeatureRolloutId) || throw new \InvalidArgumentException();
    }
}
