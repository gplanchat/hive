<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Role\Query;

use ApiPlatform\Metadata\Operation;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\Query\UseCases\QuerySeveralRole;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class QuerySeveralRoleDenormalizer implements DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            QuerySeveralRole::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $operation =  $data['operation'];
        assert($operation instanceof Operation);

        return new QuerySeveralRole(
            RealmId::fromString($data['id']),
            max((int) ($data['filters']['page'] ?? 1), 1),
            min(max((int) ($data['filters']['itemsPerPage'] ?? $operation->getPaginationItemsPerPage() ?? 25), 10), $operation->getPaginationMaximumItemsPerPage()),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === QuerySeveralRole::class;
    }
}
