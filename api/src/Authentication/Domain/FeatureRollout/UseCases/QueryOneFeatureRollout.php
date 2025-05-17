<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\UserInterface\FeatureRollout\QueryOneFeatureRolloutProvider;
use App\Platform\Infrastructure\ApiPlatform\QueryOne;

#[QueryOne(
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
    class: FeatureRollout::class,
    input: QueryOneFeatureRollout::class,
    output: FeatureRollout::class,
    validate: true,
    provider: QueryOneFeatureRolloutProvider::class,
)]
final readonly class QueryOneFeatureRollout
{
    public function __construct(
        public FeatureRolloutId $code,
    ) {
    }
}
