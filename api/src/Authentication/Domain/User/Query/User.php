<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\Query\UseCases\QueryOneUser;
use App\Authentication\Domain\User\Query\UseCases\QuerySeveralUser;
use App\Authentication\Domain\User\Query\UseCases\QuerySeveralUserInOrganization;
use App\Authentication\Domain\User\Query\UseCases\QuerySeveralUserInWorkspace;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\User\QueryOneUserProvider;
use App\Authentication\UserInterface\User\QuerySeveralUserProvider;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;

#[Get(
    uriTemplate: '/authentication/users/{uuid}',
    uriVariables: ['uuid'],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the User',
                required: true,
                schema: ['pattern' => UserId::REQUIREMENT],
            ),
        ],
    ),
    input: QueryOneUser::class,
    provider: QueryOneUserProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/users',
    openapi: new Operation(),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['uuid' => 'ASC'],
    input: QuerySeveralUser::class,
    provider: QuerySeveralUserProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
#[GetCollection(
    uriTemplate: '/authentication/organizations/{organizationId}/users',
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
    input: QuerySeveralUserInOrganization::class,
    provider: QuerySeveralUserProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
#[GetCollection(
    uriTemplate: '/authentication/workspaces/{workspaceId}/users',
    uriVariables: [
//        'workspaceId' => new Link('workspaceId', fromClass: self::class, toClass: Workspace::class),
        'workspaceId',
    ],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'workspaceId',
                in: 'path',
                description: 'Identifier of the Workspace',
                required: true,
                schema: ['pattern' => UserId::REQUIREMENT],
            ),
        ],
    ),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['uuid' => 'ASC'],
    input: QuerySeveralUserInWorkspace::class,
    provider: QuerySeveralUserProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
final class User
{
    /**
     * @param WorkspaceId[] $workspaceIds
     * @param RoleId[] $roleIds
     */
    public function __construct(
        #[ApiProperty(
            description: 'Identifier of the User',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => UserId::REQUIREMENT],
        )]
        public UserId $uuid,
        #[ApiProperty(
            description: 'Identifier of the Owning Organization',
            schema: ['type' => 'string', 'pattern' => OrganizationId::URI_REQUIREMENT],
        )]
        #[Context(['iri_only' => true])]
        public OrganizationId $organizationId,
        #[ApiProperty(
            description: 'Identifier of the Owning Organization',
            schema: ['type' => 'list', 'items' => ['type' => 'string', 'pattern' => WorkspaceId::URI_REQUIREMENT]],
        )]
        #[Context(['iri_only' => true])]
        public array $workspaceIds,
        #[ApiProperty(
            description: 'Identifiers of the assigned roles',
            schema: ['type' => 'list', 'items' => ['type' => 'string', 'pattern' => RoleId::URI_REQUIREMENT]],
        )]
        #[Context(['iri_only' => true])]
        public array $roleIds,
        #[ApiProperty(
            description: 'User\'s display name',
            schema: ['type' => 'string'],
        )]
        public string $username,
        #[ApiProperty(
            description: 'User\'s first name',
            schema: ['type' => 'string'],
        )]
        public string $firstName,
        #[ApiProperty(
            description: 'User\'s last name',
            schema: ['type' => 'string'],
        )]
        public string $lastName,
        #[ApiProperty(
            description: 'User\'s email address',
            schema: ['type' => 'string', 'format' => 'email'],
        )]
        public string $email,
        #[ApiProperty(
            description: 'Wether the User is enabled or not',
            schema: ['type' => 'boolean'],
        )]
        public bool $enabled = true,
    ) {
        array_all($this->workspaceIds, fn ($workspaceId) => $workspaceId instanceof WorkspaceId) || throw new \InvalidArgumentException();
        array_all($this->roleIds, fn ($roleId) => $roleId instanceof RoleId) || throw new \InvalidArgumentException();
    }
}
