<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\UserInterface\Organization\RemoveFeatureRolloutsFromOrganizationInput;
use App\Shared\Infrastructure\Collection\Collection;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class RemoveFeatureRolloutsFromOrganizationInputDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return \in_array($format, ['json', 'jsonld'], true) ? [
            RemoveFeatureRolloutsFromOrganizationInput::class => false,
        ] : [];
    }

    /**
     * @param array{} $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): RemoveFeatureRolloutsFromOrganizationInput
    {
        if (!\array_key_exists('featureRolloutIds', $data)
            || !\is_array($data['featureRolloutIds'])
            || !array_is_list($data['featureRolloutIds'])
        ) {
            throw new UnexpectedValueException();
        }

        return new RemoveFeatureRolloutsFromOrganizationInput(
            featureRolloutIds: Collection::fromArray($data['featureRolloutIds'])
                ->map(fn (string $uri): FeatureRolloutId => FeatureRolloutId::fromUri($uri))
                ->toArray(),
        );
    }

    /**
     * @param array{} $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return RemoveFeatureRolloutsFromOrganizationInput::class === $type;
    }
}
