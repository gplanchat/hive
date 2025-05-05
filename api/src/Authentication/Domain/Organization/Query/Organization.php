<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\UseCases\QueryOneOrganization;
use App\Authentication\Domain\Organization\Query\UseCases\QuerySeveralOrganization;
use App\Authentication\UserInterface\Organization\QueryOneOrganizationProvider;
use App\Authentication\UserInterface\Organization\QuerySeveralOrganizationProvider;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;

#[Get(
    uriTemplate: '/authentication/organizations/{uuid}',
    uriVariables: ['uuid'],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the Organization',
                required: true,
                schema: ['pattern' => OrganizationId::REQUIREMENT],
            ),
        ],
    ),
    input: QueryOneOrganization::class,
    provider: QueryOneOrganizationProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/organizations',
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['uuid' => 'ASC'],
    input: QuerySeveralOrganization::class,
    provider: QuerySeveralOrganizationProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
final readonly class Organization
{
    public function __construct(
        #[ApiProperty(
            description: 'Identifier of the Organization',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => OrganizationId::REQUIREMENT],
        )]
        public OrganizationId $uuid,
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
