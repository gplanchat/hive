<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\IdInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\Query\Workspace;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class IdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return \in_array($format, ['json', 'jsonld'], true) ? [
            'object' => false,
        ] : [];
    }

    /**
     * @param array{} $context
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        if (!$data instanceof IdInterface) {
            throw new InvalidArgumentException('The provided data type is not supported for normalization.');
        }

        if (!\array_key_exists('iri_only', $context) || false === $context['iri_only']) {
            return $data->toString();
        }

        $resource = $context['object'] ?? null;

        return match ($data::class) {
            FeatureRolloutId::class => $this->iriConverter->getIriFromResource(FeatureRollout::class, context: [
                'uri_variables' => [
                    'code' => $data->toString(),
                ],
            ]) ?: throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
            OrganizationId::class => $this->iriConverter->getIriFromResource(Organization::class, context: [
                'uri_variables' => [
                    'realm' => \is_object($resource) ? match ($resource::class) {
                        Organization::class => $resource->realmId->toString(),
                        Role::class => $resource->realmId->toString(),
                        User::class => $resource->realmId->toString(),
                        Workspace::class => $resource->realmId->toString(),
                        default => throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
                    } : null,
                    'uuid' => $data->toString(),
                ],
            ]) ?: throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
            UserId::class => $this->iriConverter->getIriFromResource(User::class, context: [
                'uri_variables' => [
                    'realm' => \is_object($resource) ? match ($resource::class) {
                        Organization::class => $resource->realmId->toString(),
                        Role::class => $resource->realmId->toString(),
                        User::class => $resource->realmId->toString(),
                        Workspace::class => $resource->realmId->toString(),
                        default => throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
                    } : null,
                    'uuid' => $data->toString(),
                ],
            ]) ?: throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
            RoleId::class => $this->iriConverter->getIriFromResource(Role::class, context: [
                'uri_variables' => [
                    'realm' => \is_object($resource) ? match ($resource::class) {
                        Organization::class => $resource->realmId->toString(),
                        Role::class => $resource->realmId->toString(),
                        User::class => $resource->realmId->toString(),
                        Workspace::class => $resource->realmId->toString(),
                        default => throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
                    } : null,
                    'uuid' => $data->toString(),
                ],
            ]) ?: throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
            WorkspaceId::class => $this->iriConverter->getIriFromResource(Workspace::class, context: [
                'uri_variables' => [
                    'realm' => \is_object($resource) ? match ($resource::class) {
                        Organization::class => $resource->realmId->toString(),
                        Role::class => $resource->realmId->toString(),
                        User::class => $resource->realmId->toString(),
                        Workspace::class => $resource->realmId->toString(),
                        default => throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
                    } : null,
                    'uuid' => $data->toString(),
                ],
            ]) ?: throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
            default => throw new InvalidArgumentException('The provided data type is not supported for normalization.'),
        };
    }

    /**
     * @param array{} $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof IdInterface;
    }

    /**
     * @param class-string<IdInterface> $type
     * @param array{}                   $context
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): IdInterface
    {
        if (!\is_string($data)
            || !class_exists($type)
            || !is_a($type, IdInterface::class, true)
        ) {
            throw new InvalidArgumentException('The provided data type is not supported for denormalization');
        }

        if (!\array_key_exists('iri_only', $context) || false === $context['iri_only']) {
            return $type::fromString($data);
        }

        return $type::fromUri($data);
    }

    /**
     * @param array{} $context
     */
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return \is_string($data)
            && is_a($type, IdInterface::class, true);
    }
}
