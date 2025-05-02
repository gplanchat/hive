<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Query;

use App\Authentication\Domain\User\Query\UseCase\GetSeveralUser;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class GetSeveralUserDenormalizer implements DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            GetSeveralUser::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return new GetSeveralUser(
            max((int) ($data['filters']['page'] ?? 1), 1),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === GetSeveralUser::class;
    }
}
