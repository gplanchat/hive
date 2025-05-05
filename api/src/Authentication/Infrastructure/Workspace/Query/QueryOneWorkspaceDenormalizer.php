<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace\Query;

use App\Authentication\Domain\Workspace\Query\UseCases\QueryOneWorkspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

#[AutoconfigureTag('serializer.normalizer')]
final class QueryOneWorkspaceDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            QueryOneWorkspace::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new QueryOneWorkspace(
            $this->denormalizer->denormalize($data['uri_variables']['uuid'], WorkspaceId::class, $format, $context),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === QueryOneWorkspace::class;
    }
}
