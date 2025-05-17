<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Role\Query\Role;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\Query\Workspace;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class UserIdNormalizer implements NormalizerInterface, DenormalizerInterface
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
        if (!$data instanceof UserId) {
            throw new InvalidArgumentException('The provided data type is not supported for normalization.');
        }

        if (!\array_key_exists('iri_only', $context) || false === $context['iri_only']) {
            return $data->toString();
        }

        $resource = $context['object'] ?? null;

        return $this->iriConverter->getIriFromResource(User::class, context: [
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
        ]) ?: throw new InvalidArgumentException('The provided data type is not supported for normalization.');
    }

    /**
     * @param array{} $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UserId;
    }

    /**
     * @param class-string<UserId> $type
     * @param array{}              $context
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): UserId
    {
        if (!\is_string($data)
            || \strlen($data) <= 0
            || !class_exists($type)
            || !is_a($type, UserId::class, true)
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
            && is_a($type, UserId::class, true);
    }
}
