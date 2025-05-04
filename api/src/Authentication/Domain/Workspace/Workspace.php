<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\UseCases\GetOneWorkspace;
use App\Authentication\Domain\Workspace\UseCases\GetSeveralWorkspace;
use App\Authentication\Domain\Workspace\UseCases\GetSeveralWorkspaceInOrganization;
use App\Authentication\UserInterface\Workspace\GetOneWorkspaceProvider;
use App\Authentication\UserInterface\Workspace\GetSeveralWorkspaceProvider;
use Symfony\Component\Routing\Requirement\Requirement;

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
                schema: ['pattern' => Requirement::UUID_V7],
            ),
        ],
    ),
    input: GetOneWorkspace::class,
    provider: GetOneWorkspaceProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/workspaces',
    openapi: new Operation(),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    input: GetSeveralWorkspace::class,
    provider: GetSeveralWorkspaceProvider::class,
    parameters: [
        'page' => new QueryParameter(),
    ],
    itemUriTemplate: '/authentication/workspaces/{uuid}',
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
                schema: ['pattern' => Requirement::UUID_V7],
            ),
        ],
    ),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    input: GetSeveralWorkspaceInOrganization::class,
    provider: GetSeveralWorkspaceProvider::class,
    parameters: [
        'page' => new QueryParameter(),
    ],
    itemUriTemplate: '/authentication/workspaces/{uuid}',
)]
final readonly class Workspace
{
    public function __construct(
        #[ApiProperty(
            description: 'Identifier of the Workspace',
            identifier: true,
            schema: ['pattern' => WorkspaceId::REQUIREMENT],
        )]
        public WorkspaceId $uuid,
        #[ApiProperty(
            description: 'Identifier of the Owning Organization',
            schema: ['type' => 'string', 'pattern' => OrganizationId::REQUIREMENT],
        )]
        public OrganizationId $organizationId,
    ) {}
}
