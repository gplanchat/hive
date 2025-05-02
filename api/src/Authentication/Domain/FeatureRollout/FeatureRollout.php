<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\FeatureRollout\UseCases\GetOneFeatureRollout;
use App\Authentication\Domain\FeatureRollout\UseCases\GetSeveralFeatureRollout;
use App\Authentication\UserInterface\FeatureRollout\QueryOneFeatureRolloutProvider;
use App\Authentication\UserInterface\FeatureRollout\QuerySeveralFeatureRolloutProvider;
use Symfony\Component\Routing\Requirement\Requirement;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/authentication/feature-rollouts/{code}',
            uriVariables: ['code'],
            openapi: new Operation(
                summary: 'Get feature rollout',
                parameters: [
                    new Parameter(
                        name: 'code',
                        in: 'path',
                        description: 'Code of the Feature Rollout',
                        required: true,
                        schema: ['pattern' => Requirement::UUID_V7],
                    ),
                ],
            ),
            input: GetOneFeatureRollout::class,
            output: FeatureRollout::class,
            provider: QueryOneFeatureRolloutProvider::class
        ),
        new GetCollection(
            uriTemplate: '/authentication/feature-rollouts',
            paginationEnabled: true,
            paginationItemsPerPage: 25,
            paginationMaximumItemsPerPage: 100,
            paginationPartial: true,
            input: GetSeveralFeatureRollout::class,
            provider: QuerySeveralFeatureRolloutProvider::class,
            itemUriTemplate: '/authentication/feature-rollouts/{code}',
        ),
//        new Put(
//            uriTemplate: '/authentication/feature-rollouts/{code}',
//            uriVariables: ['code'],
//            openapi: new Operation(
//                parameters: [
//                    new Parameter(
//                        name: 'code',
//                        in: 'path',
//                        description: 'Code of the Feature Rollout',
//                        required: true,
//                        schema: ['pattern' => Requirement::UUID_V7],
//                    ),
//                ],
//            ),
//        ),
    ],
    order: ['code' => 'ASC'],
)]
final readonly class FeatureRollout
{
    public function __construct(
        #[ApiProperty(
            description: 'Code of the Feature Rollout',
            writable: true,
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
