<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\FeatureRollout\UseCases\QueryOneFeatureRollout;
use App\Authentication\UserInterface\FeatureRollout\QueryOneFeatureRolloutProvider;
use App\Authentication\UserInterface\FeatureRollout\QuerySeveralFeatureRolloutProvider;

#[Get(
    uriTemplate: '/feature-rollouts/{code}',
    uriVariables: ['code'],
    openapi: new Operation(
        summary: 'Get feature rollout',
        parameters: [
            new Parameter(
                name: 'code',
                in: 'path',
                description: 'Code of the Feature Rollout',
                required: true,
                schema: ['type' => 'string', 'pattern' => FeatureRolloutId::REQUIREMENT],
            ),
        ],
    ),
    input: QueryOneFeatureRollout::class,
    output: FeatureRollout::class,
    validate: true,
    provider: QueryOneFeatureRolloutProvider::class,
)]
#[GetCollection(
    uriTemplate: '/feature-rollouts',
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['code' => 'ASC'],
    validate: true,
    provider: QuerySeveralFeatureRolloutProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
final readonly class FeatureRollout
{
    public function __construct(
        #[ApiProperty(
            description: 'Code of the Feature Rollout',
            writable: true,
            required: true,
            identifier: true,
            schema: [
                'type' => 'string',
                'pattern' => FeatureRolloutId::REQUIREMENT,
                'minLength' => 3,
                'maxLength' => 100,
            ],
        )]
        public FeatureRolloutId $code,
    ) {
    }
}
