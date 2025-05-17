<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Workspace\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\UserInterface\Workspace\CreateWorkspaceInput;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class CreateWorkspaceInputDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return \in_array($format, ['json', 'jsonld'], true) ? [
            CreateWorkspaceInput::class => false,
        ] : [];
    }

    /**
     * @param array{} $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): CreateWorkspaceInput
    {
        if (!\array_key_exists('name', $data) || !\is_string($data['name'])
            || !\array_key_exists('slug', $data) || !\is_string($data['slug'])
            || !\array_key_exists('organizationId', $data) || !\is_string($data['organizationId'])
        ) {
            throw new UnexpectedValueException();
        }

        return new CreateWorkspaceInput(
            organizationId: \strlen($data['organizationId']) > 0
                ? OrganizationId::fromUri($data['organizationId'])
                : throw new UnexpectedValueException('Organization id can\'t be empty'),
            name: $data['name'],
            slug: $data['slug'],
            validUntil: \array_key_exists('validUntil', $data) && null != $data['validUntil']
                ? (\DateTimeImmutable::createFromFormat('Y-m-d', $data['validUntil'], new \DateTimeZone('UTC')) ?: null)
                : null,
            enabled: (bool) ($data['enabled'] ?? false),
        );
    }

    /**
     * @param array{} $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return CreateWorkspaceInput::class === $type;
    }
}
