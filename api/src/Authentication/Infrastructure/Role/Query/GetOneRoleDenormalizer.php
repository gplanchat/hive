<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Query;

use App\Authentication\Domain\Role\Query\UseCases\GetOneRole;
use App\Authentication\Domain\Role\RoleId;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

#[AutoconfigureTag('serializer.normalizer')]
final class GetOneRoleDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            GetOneRole::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new GetOneRole(
            $this->denormalizer->denormalize($data['uri_variables']['uuid'], RoleId::class, $format, $context),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === GetOneRole::class;
    }
}
