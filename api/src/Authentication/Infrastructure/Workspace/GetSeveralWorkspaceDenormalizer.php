<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace;

use App\Authentication\Domain\Workspace\UseCases\GetSeveralWorkspace;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class GetSeveralWorkspaceDenormalizer implements DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            GetSeveralWorkspace::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new GetSeveralWorkspace(
            max((int) ($data['filters']['page'] ?? 1), 1),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === GetSeveralWorkspace::class;
    }
}
