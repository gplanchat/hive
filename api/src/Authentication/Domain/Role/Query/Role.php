<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\Query\UseCases\GetOneRole;
use App\Authentication\Domain\Role\Query\UseCases\GetSeveralRole;
use App\Authentication\Domain\Role\Query\UseCases\GetSeveralRoleInOrganization;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\UserInterface\Role\GetOneRoleProvider;
use App\Authentication\UserInterface\Role\GetSeveralRoleProvider;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;

#[Get(
    uriTemplate: '/authentication/roles/{uuid}',
    uriVariables: ['uuid'],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the Role',
                required: true,
                schema: ['pattern' => Requirement::UUID_V7],
            ),
        ],
    ),
    input: GetOneRole::class,
    provider: GetOneRoleProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/roles',
    openapi: new Operation(),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    input: GetSeveralRole::class,
    provider: GetSeveralRoleProvider::class,
    parameters: [
        'page' => new QueryParameter(),
    ],
)]
#[GetCollection(
    uriTemplate: '/authentication/organizations/{organizationId}/roles',
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
    input: GetSeveralRoleInOrganization::class,
    provider: GetSeveralRoleProvider::class,
    parameters: [
        'page' => new QueryParameter(),
    ],
)]
final readonly class Role
{
    /** @var ResourceAccess[] */
    public array $resourceAccesses;

    public function __construct(
        #[ApiProperty(
            description: 'Identifier of the Role',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => RoleId::REQUIREMENT],
        )]
        public RoleId $uuid,
        #[ApiProperty(
            description: 'Identifier of the Owning Organization',
            schema: ['type' => 'string', 'pattern' => OrganizationId::REQUIREMENT],
        )]
        #[Context(['iri_only' => true])]
        public OrganizationId $organizationId,
        #[ApiProperty(
            description: 'Identifier of the Role',
            schema: ['type' => 'string', 'minLength' => 3, 'maxLength' => 150, 'pattern' => Requirement::ASCII_SLUG],
        )]
        public string $identifier,
        #[ApiProperty(
            description: 'Label of the Role',
            schema: ['type' => 'string', 'minLength' => 3, 'maxLength' => 150],
        )]
        public string $label,
        #[ApiProperty(
            description: 'Resource accesses specifications',
            schema: ['type' => 'string', 'minLength' => 3, 'maxLength' => 150],
        )]
        array $resourceAccesses = []
    ) {
        $this->resourceAccesses = $resourceAccesses;
    }
}
