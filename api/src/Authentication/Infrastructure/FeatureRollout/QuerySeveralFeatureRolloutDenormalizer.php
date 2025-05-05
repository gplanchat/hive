<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\FeatureRollout;

use ApiPlatform\Metadata\Operation;
use App\Authentication\Domain\FeatureRollout\UseCases\QuerySeveralFeatureRollout;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class QuerySeveralFeatureRolloutDenormalizer implements DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            QuerySeveralFeatureRollout::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $operation =  $data['operation'];
        assert($operation instanceof Operation);

        return new QuerySeveralFeatureRollout(
            max((int) ($data['filters']['page'] ?? 1), 1),
            min(max((int) ($data['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 10), 1), $operation->getPaginationMaximumItemsPerPage()),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === QuerySeveralFeatureRollout::class;
    }
}
