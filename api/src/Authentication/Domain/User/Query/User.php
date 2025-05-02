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
use App\Authentication\Domain\User\Query\UseCase\GetOneUser;
use App\Authentication\Domain\User\Query\UseCase\GetSeveralUser;
use App\Authentication\Domain\User\Query\UseCase\GetSeveralUserInOrganization;
use App\Authentication\Domain\User\Query\UseCase\GetSeveralUserInWorkspace;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\User\GetOneUserProvider;
use App\Authentication\UserInterface\User\GetSeveralUserProvider;
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
                schema: ['pattern' => Requirement::UUID_V7],
            ),
        ],
    ),
    input: GetOneUser::class,
    provider: GetOneUserProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/users',
    openapi: new Operation(),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    input: GetSeveralUser::class,
    provider: GetSeveralUserProvider::class,
    parameters: [
        'page' => new QueryParameter(),
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
                schema: ['pattern' => Requirement::UUID_V7],
            ),
        ],
    ),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    input: GetSeveralUserInOrganization::class,
    provider: GetSeveralUserProvider::class,
    parameters: [
        'page' => new QueryParameter(),
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
                schema: ['pattern' => Requirement::UUID_V7],
            ),
        ],
    ),
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    input: GetSeveralUserInWorkspace::class,
    provider: GetSeveralUserProvider::class,
    parameters: [
        'page' => new QueryParameter(),
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
            schema: ['type' => 'string', 'pattern' => OrganizationId::REQUIREMENT],
        )]
        #[Context(['iri_only' => true])]
        public OrganizationId $organizationId,
        #[ApiProperty(
            description: 'Identifier of the Owning Organization',
            schema: ['type' => 'list', 'items' => ['type' => 'string', 'pattern' => WorkspaceId::REQUIREMENT]],
        )]
        #[Context(['iri_only' => true])]
        public array $workspaceIds,
        #[ApiProperty(
            description: 'Identifiers of the assigned roles',
            schema: ['type' => 'list', 'items' => ['type' => 'string', 'pattern' => RoleId::REQUIREMENT]],
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
