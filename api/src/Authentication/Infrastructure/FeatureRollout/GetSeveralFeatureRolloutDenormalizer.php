<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\FeatureRollout;

use ApiPlatform\Metadata\Operation;
use App\Authentication\Domain\FeatureRollout\UseCases\GetSeveralFeatureRollout;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class GetSeveralFeatureRolloutDenormalizer implements DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            GetSeveralFeatureRollout::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $operation =  $data['operation'];
        assert($operation instanceof Operation);

        return new GetSeveralFeatureRollout(
            max((int) ($data['filters']['page'] ?? 1), 1),
            min(max((int) ($data['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 10), 1), $operation->getPaginationMaximumItemsPerPage()),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === GetSeveralFeatureRollout::class;
    }
}
