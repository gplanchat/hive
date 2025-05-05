<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Query;

use ApiPlatform\Metadata\Operation;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\Query\UseCases\QuerySeveralRoleInOrganization;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class QuerySeveralRoleInOrganizationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            QuerySeveralRoleInOrganization::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $operation =  $data['operation'];
        assert($operation instanceof Operation);

        return new QuerySeveralRoleInOrganization(
            $this->denormalizer->denormalize($data['uri_variables']['organizationId'], OrganizationId::class, $format, $context),
            max((int) ($data['filters']['page'] ?? 1), 1),
            min(max((int) ($data['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 25), 10), $operation->getPaginationMaximumItemsPerPage()),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === QuerySeveralRoleInOrganization::class;
    }
}
