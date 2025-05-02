<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Query;

use App\Authentication\Domain\Role\Query\UseCases\GetSeveralRole;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class GetSeveralRoleDenormalizer implements DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            GetSeveralRole::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new GetSeveralRole(
            max((int) ($data['filters']['page'] ?? 1), 1),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === GetSeveralRole::class;
    }
}
