<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Query;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\UseCases\QueryOneOrganization;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

#[AutoconfigureTag('serializer.normalizer')]
final class QueryOneOrganizationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            QueryOneOrganization::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new QueryOneOrganization(
            $this->denormalizer->denormalize($data['uri_variables']['uuid'], OrganizationId::class, $format, $context),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === QueryOneOrganization::class;
    }
}
