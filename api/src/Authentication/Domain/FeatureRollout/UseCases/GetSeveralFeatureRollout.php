<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout\UseCases;

use ApiPlatform\Metadata\ApiProperty;
use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Utils\Operations\GetSeveral;

#[GetSeveral(
    resource: FeatureRollout::class,
    uriTemplate: '/authentication/features-rollout',
)]
final readonly class GetSeveralFeatureRollout
{
    public function __construct(
        #[ApiProperty(readable: false)]
        public int $currentPage = 1,
        #[ApiProperty(readable: false)]
        public int $itemsPerPage = 25,
    ) {
    }
}
