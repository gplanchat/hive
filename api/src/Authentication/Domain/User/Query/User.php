<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\Query\UseCases\QueryOneUser;
use App\Authentication\Domain\User\Query\UseCases\QuerySeveralUser;
use App\Authentication\Domain\User\Query\UseCases\QuerySeveralUserInOrganization;
use App\Authentication\Domain\User\Query\UseCases\QuerySeveralUserInWorkspace;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\User\CreateUserInput;
use App\Authentication\UserInterface\User\CreateUserProcessor;
use App\Authentication\UserInterface\User\CreateUserWithinOrganizationInput;
use App\Authentication\UserInterface\User\DeleteUserProcessor;
use App\Authentication\UserInterface\User\DisableUserInput;
use App\Authentication\UserInterface\User\DisableUserProcessor;
use App\Authentication\UserInterface\User\EnableUserInput;
use App\Authentication\UserInterface\User\EnableUserProcessor;
use App\Authentication\UserInterface\User\QueryOneUserProvider;
use App\Authentication\UserInterface\User\QuerySeveralUserProvider;
use Symfony\Component\Serializer\Attribute\Context;

#[Get(
    uriTemplate: '/authentication/{realm}/users/{uuid}',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the User',
                required: true,
                schema: ['pattern' => UserId::REQUIREMENT],
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
    provider: QueryOneUserProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/users',
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
    provider: QuerySeveralUserProvider::class,
    itemUriTemplate: '/authentication/{realm}/users/{uuid}',
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/organizations/{organizationId}/users',
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
    provider: QuerySeveralUserProvider::class,
    itemUriTemplate: '/authentication/{realm}/users/{uuid}',
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/workspaces/{workspaceId}/users',
    uriVariables: [
        'realm' => 'realmId',
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
    provider: QuerySeveralUserProvider::class,
    itemUriTemplate: '/authentication/{realm}/users/{uuid}',
)]
#[Post(
    uriTemplate: '/authentication/{realm}/organizations/{organizationId}/users',
    uriVariables: ['realm', 'organizationId'],
    input: CreateUserWithinOrganizationInput::class,
    output: self::class,
    processor: CreateUserProcessor::class,
    itemUriTemplate: '/authentication/{realm}/users/{uuid}',
)]
#[Post(
    uriTemplate: '/authentication/{realm}/users',
    uriVariables: [
        'realm' => 'realmId',
    ],
    input: CreateUserInput::class,
    output: self::class,
    processor: CreateUserProcessor::class,
    itemUriTemplate: '/authentication/{realm}/users/{uuid}',
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/users/{uuid}/enable',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    input: EnableUserInput::class,
    output: self::class,
    provider: QueryOneUserProvider::class,
    processor: EnableUserProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/{realm}/users/{uuid}/disable',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    input: DisableUserInput::class,
    output: self::class,
    provider: QueryOneUserProvider::class,
    processor: DisableUserProcessor::class,
)]
#[Delete(
    uriTemplate: '/authentication/{realm}/users/{uuid}',
    uriVariables: ['realm', 'uuid'],
    input: false,
    output: false,
    provider: QueryOneUserProvider::class,
    processor: DeleteUserProcessor::class,
)]
final readonly class User
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
            description: 'Realm of the User',
            identifier: true,
            schema: ['type' => 'string', 'pattern' => RealmId::REQUIREMENT],
        )]
        public RealmId $realmId,
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
