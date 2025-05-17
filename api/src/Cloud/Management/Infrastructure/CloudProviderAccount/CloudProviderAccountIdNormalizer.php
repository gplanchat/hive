<?php

declare(strict_types=1);

namespace App\Cloud\Management\Infrastructure\CloudProviderAccount;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccount;
use App\Platform\Domain\IdInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CloudProviderAccountIdNormalizer implements NormalizerInterface, DenormalizerInterface
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
        if (!$data instanceof CloudProviderAccountId) {
            throw new InvalidArgumentException('The provided data type is not supported for normalization.');
        }

        if (!\array_key_exists('iri_only', $context) || false === $context['iri_only']) {
            return $data->toString();
        }

        return $this->iriConverter->getIriFromResource(CloudProviderAccount::class, context: [
            'uri_variables' => [
                'code' => $data->toString(),
            ],
        ]) ?: throw new InvalidArgumentException('The provided data type is not supported for normalization.');
    }

    /**
     * @param array{} $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof CloudProviderAccountId;
    }

    /**
     * @param class-string<IdInterface> $type
     * @param array{}                   $context
     */
    public function denormalize($data, string $type, ?string $format = null, array $context = []): IdInterface
    {
        if (!\is_string($data)
            || \strlen($data) <= 0
            || !class_exists($type)
            || !is_a($type, CloudProviderAccountId::class, true)
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
            && is_a($type, CloudProviderAccountId::class, true);
    }
}
