<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\ResourceAccess;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\UserInterface\Role\CreateRoleInput;
use App\Authentication\UserInterface\Role\CreateRoleProcessor;
use App\Authentication\UserInterface\Role\CreateRoleWithinOrganizationInput;
use App\Authentication\UserInterface\Role\DeleteRoleProcessor;
use App\Authentication\UserInterface\Role\QueryOneRoleProvider;
use App\Authentication\UserInterface\Role\QuerySeveralRoleInOrganizationProvider;
use App\Authentication\UserInterface\Role\QuerySeveralRoleProvider;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Attribute\Context;

#[Get(
    uriTemplate: '/authentication/{realm}/roles/{uuid}',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    openapi: new Operation(
        parameters: [
            new Parameter(
                name: 'uuid',
                in: 'path',
                description: 'Identifier of the Role',
                required: true,
                schema: ['pattern' => RoleId::REQUIREMENT],
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
    provider: QueryOneRoleProvider::class
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/roles',
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
    provider: QuerySeveralRoleProvider::class,
    itemUriTemplate: '/authentication/{realm}/roles/{uuid}',
)]
#[GetCollection(
    uriTemplate: '/authentication/{realm}/organizations/{organizationId}/roles',
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
                schema: ['pattern' => Requirement::UUID_V7],
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
    provider: QuerySeveralRoleInOrganizationProvider::class,
    itemUriTemplate: '/authentication/{realm}/roles/{uuid}'
)]
#[Post(
    uriTemplate: '/authentication/{realm}/organizations/{organizationId}/roles',
    uriVariables: [
        'realm' => 'realmId',
        'organizationId',
    ],
    input: CreateRoleWithinOrganizationInput::class,
    output: self::class,
    processor: CreateRoleProcessor::class,
    itemUriTemplate: '/authentication/{realm}/roles/{uuid}',
)]
#[Post(
    uriTemplate: '/authentication/{realm}/roles',
    uriVariables: [
        'realm' => 'realmId',
    ],
    input: CreateRoleInput::class,
    output: self::class,
    processor: CreateRoleProcessor::class,
    itemUriTemplate: '/authentication/{realm}/roles/{uuid}',
)]
#[Delete(
    uriTemplate: '/authentication/{realm}/roles/{uuid}',
    uriVariables: [
        'realm' => 'realmId',
        'uuid',
    ],
    input: false,
    output: false,
    provider: QueryOneRoleProvider::class,
    processor: DeleteRoleProcessor::class,
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
            description: 'Realm of the Role',
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
