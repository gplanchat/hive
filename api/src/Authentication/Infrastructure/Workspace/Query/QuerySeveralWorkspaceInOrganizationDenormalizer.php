<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace\Query;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\Query\UseCases\QuerySeveralWorkspaceInOrganization;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class QuerySeveralWorkspaceInOrganizationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            QuerySeveralWorkspaceInOrganization::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $operation = $context['operation'];
        assert($operation instanceof Operation);

        return new QuerySeveralWorkspaceInOrganization(
            $this->denormalizer->denormalize($data['uri_variables']['organizationId'], OrganizationId::class, $format, $context),
            max((int) ($data['filters']['page'] ?? 1), 1),
            min(max((int) ($data['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 25), 10), $operation->getPaginationMaximumItemsPerPage()),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === QuerySeveralWorkspaceInOrganization::class;
    }
}
