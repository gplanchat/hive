<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Query;

use ApiPlatform\Metadata\Operation;
use App\Authentication\Domain\Organization\Query\UseCases\QuerySeveralOrganization;
use App\Authentication\Domain\Realm\RealmId;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class QuerySeveralOrganizationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            QuerySeveralOrganization::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $operation = $context['operation'];
        assert($operation instanceof Operation);

        return new QuerySeveralOrganization(
            $this->denormalizer->denormalize($data['uri_variables']['realm'], RealmId::class, $format, $context),
            max((int) ($data['filters']['page'] ?? 1), 1),
            min(max((int) ($data['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 25), 10), $operation->getPaginationMaximumItemsPerPage()),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === QuerySeveralOrganization::class;
    }
}
