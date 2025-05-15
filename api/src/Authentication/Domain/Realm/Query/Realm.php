<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Realm\Query;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\Realm\Query\UseCases\QueryOneRealm;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\UserInterface\Realm\QueryOneRealmProvider;
use App\Authentication\UserInterface\Realm\QuerySeveralRealmProvider;

#[Get(
    uriTemplate: '/realms/{code}',
    uriVariables: ['code'],
    openapi: new Operation(
        summary: 'Get Realm',
        parameters: [
            new Parameter(
                name: 'code',
                in: 'path',
                description: 'Code of the Realm',
                required: true,
                schema: ['type' => 'string', 'pattern' => RealmId::REQUIREMENT],
            ),
        ],
    ),
    security: 'is_granted("IS_AUTHENTICATED")',
    input: QueryOneRealm::class,
    output: Realm::class,
    validate: true,
    provider: QueryOneRealmProvider::class,
)]
#[GetCollection(
    uriTemplate: '/realms',
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['code' => 'ASC'],
    security: 'is_granted("IS_AUTHENTICATED")',
    validate: true,
    provider: QuerySeveralRealmProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
final readonly class Realm
{
    public function __construct(
        #[ApiProperty(
            description: 'Code of the Realm',
            writable: true,
            required: true,
            identifier: true,
            schema: [
                'type' => 'string',
                'pattern' => RealmId::REQUIREMENT,
                'minLength' => 3,
                'maxLength' => 100,
            ],
        )]
        public RealmId $code,

        #[ApiProperty(
            description: 'Display name of the Realm',
            writable: true,
            required: true,
            schema: ['type' => 'string'],
        )]
        public string $displayName,
    ) {
    }
}
