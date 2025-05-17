<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\UserInterface\Organization\CreateOrganizationInput;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class CreateOrganizationInputDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return \in_array($format, ['json', 'jsonld'], true) ? [
            CreateOrganizationInput::class => false,
        ] : [];
    }

    /**
     * @param array{} $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): CreateOrganizationInput
    {
        if (!\array_key_exists('name', $data) || !\is_string($data['name'])
            || !\array_key_exists('slug', $data) || !\is_string($data['slug'])
        ) {
            throw new UnexpectedValueException();
        }

        return new CreateOrganizationInput(
            $data['name'],
            $data['slug'],
            \array_key_exists('validUntil', $data) && null != $data['validUntil']
                ? (\DateTimeImmutable::createFromFormat('Y-m-d', $data['validUntil'], new \DateTimeZone('UTC')) ?: null)
                : null,
            array_map(fn (string $current): FeatureRolloutId => \strlen($current) > 0 ? FeatureRolloutId::fromUri($current) : throw new \InvalidArgumentException(), $data['featureRolloutIds'] ?? []),
            (bool) ($data['enabled'] ?? false),
        );
    }

    /**
     * @param array{} $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return CreateOrganizationInput::class === $type;
    }
}
