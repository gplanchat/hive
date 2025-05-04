<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Workspace\UseCases\GetSeveralWorkspaceInOrganization;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class GetSeveralWorkspaceInOrganizationDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            GetSeveralWorkspaceInOrganization::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new GetSeveralWorkspaceInOrganization(
            $this->denormalizer->denormalize($data['uri_variables']['organizationId'], OrganizationId::class, $format, $context),
            max((int) ($data['filters']['page'] ?? 1), 1),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === GetSeveralWorkspaceInOrganization::class;
    }
}
