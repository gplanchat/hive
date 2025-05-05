<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\Query\UseCases\QueryOneWorkspace;
use App\Authentication\Domain\Workspace\Query\UseCases\QuerySeveralWorkspace;
use App\Authentication\Domain\Workspace\Query\UseCases\QuerySeveralWorkspaceInOrganization;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\Workspace\QueryOneWorkspaceProvider;
use App\Authentication\UserInterface\Workspace\QuerySeveralWorkspaceProvider;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;

#[Get(
    uriTemplate: '/authentication/workspaces/{uuid}',
    uriVariables: ['uuid'],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the Workspace',
                required: true,
                schema: ['pattern' => WorkspaceId::REQUIREMENT],
            ),
        ],
    ),
    input: QueryOneWorkspace::class,
    provider: QueryOneWorkspaceProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/workspaces',
    openapi: new Operation(),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['uuid' => 'ASC'],
    input: QuerySeveralWorkspace::class,
    provider: QuerySeveralWorkspaceProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
#[GetCollection(
    uriTemplate: '/authentication/organizations/{organizationId}/workspaces',
    uriVariables: [
//        'organizationId' => new Link('organizationId', fromClass: self::class, toClass: Organization::class),
        'organizationId',
    ],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'organizationId',
                in: 'path',
                description: 'Identifier of the Organization',
                required: true,
                schema: ['pattern' => OrganizationId::REQUIREMENT],
            ),
        ],
    ),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['uuid' => 'ASC'],
    input: QuerySeveralWorkspaceInOrganization::class,
    provider: QuerySeveralWorkspaceProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
final readonly class Workspace
{
    public function __construct(
        #[ApiProperty(
            description: 'Identifier of the Workspace',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => WorkspaceId::REQUIREMENT],
        )]
        public WorkspaceId $uuid,
        #[ApiProperty(
            description: 'Identifier of the Owning Organization',
            schema: ['type' => 'string', 'pattern' => OrganizationId::URI_REQUIREMENT],
        )]
        #[Context(['iri_only' => true])]
        public OrganizationId $organizationId,
        #[ApiProperty(
            description: 'Name of the Workspace',
            schema: ['type' => 'string'],
        )]
        public string $name,
        #[ApiProperty(
            description: 'Slug of the Workspace',
            schema: ['type' => 'string'],
        )]
        public string $slug,
        #[ApiProperty(
            description: 'End date of validity of the subscription',
            schema: ['type' => 'string', 'format' => 'date', 'required' => true, 'nullable' => true],
        )]
        #[Context(['datetime_format' => 'Y-m-d', 'skip_null_values' => false])]
        public ?\DateTimeInterface $validUntil = null,
        #[ApiProperty(
            description: 'Wether the Organization is enabled or not',
            schema: ['type' => 'boolean'],
        )]
        public bool $enabled = true,
    ) {}
}
