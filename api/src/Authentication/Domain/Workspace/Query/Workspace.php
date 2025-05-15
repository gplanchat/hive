<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Workspace\Query\Workspace as QueryWorkspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceInput;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceWithinOrganizationInput;
use App\Authentication\UserInterface\Workspace\DeleteWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\DisableWorkspaceInput;
use App\Authentication\UserInterface\Workspace\DisableWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\EnableWorkspaceInput;
use App\Authentication\UserInterface\Workspace\EnableWorkspaceProcessor;
use App\Authentication\UserInterface\Workspace\QueryOneWorkspaceProvider;
use App\Authentication\UserInterface\Workspace\QuerySeveralWorkspaceInOrganizationProvider;
use App\Authentication\UserInterface\Workspace\QuerySeveralWorkspaceProvider;
use Symfony\Component\Serializer\Attribute\Context;

#[Delete(
    uriTemplate: '/authentication/{realm}/workspaces/{uuid}',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: false,
    output: false,
    provider: QueryOneWorkspaceProvider::class,
    processor: DeleteWorkspaceProcessor::class,
)]
#[Get(
    uriTemplate: '/authentication/{realm}/workspaces/{uuid}',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the Workspace',
                required: true,
                schema: ['pattern' => WorkspaceId::REQUIREMENT],
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
    provider: QueryOneWorkspaceProvider::class,
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/workspaces',
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
    provider: QuerySeveralWorkspaceProvider::class,
    itemUriTemplate: '/authentication/{realm}/workspace/{uuid}',
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/organizations/{organizationId}/workspaces',
    uriVariables: [
        'realm' => 'realmId',
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
    provider: QuerySeveralWorkspaceInOrganizationProvider::class,
    itemUriTemplate: '/authentication/{realm}/workspace/{uuid}',
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/workspaces/{uuid}/enable',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: EnableWorkspaceInput::class,
    output: QueryWorkspace::class,
    provider: QueryOneWorkspaceProvider::class,
    processor: EnableWorkspaceProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/workspaces/{uuid}/disable',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: DisableWorkspaceInput::class,
    output: QueryWorkspace::class,
    provider: QueryOneWorkspaceProvider::class,
    processor: DisableWorkspaceProcessor::class,
)]
#[Post(
    uriTemplate: '/authentication/{realm}/workspaces',
    uriVariables: [
        'realm' => 'realmId',
    ],
    class: QueryWorkspace::class,
    security: 'is_granted("IS_AUTHENTICATED")',
    input: CreateWorkspaceInput::class,
    output: QueryWorkspace::class,
    processor: CreateWorkspaceProcessor::class,
    itemUriTemplate: '/authentication/{realm}/workspaces/{uuid}',
)]
#[Post(
    uriTemplate: '/authentication/{realm}/organizations/{organizationId}/workspaces',
    uriVariables: [
        'realm' => 'realmId',
        'organizationId',
    ],
    security: 'is_granted("IS_AUTHENTICATED")',
    input: CreateWorkspaceWithinOrganizationInput::class,
    output: QueryWorkspace::class,
    processor: CreateWorkspaceProcessor::class,
    itemUriTemplate: '/authentication/{realm}/workspaces/{uuid}',
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
            description: 'Realm of the Workspace',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => RealmId::REQUIREMENT],
        )]
        public RealmId $realmId,
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
    ) {
    }
}
