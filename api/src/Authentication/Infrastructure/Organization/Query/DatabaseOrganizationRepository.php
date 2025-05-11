<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Query;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\Organization\Query\UseCases\OrganizationPage;
use App\Authentication\Domain\Realm\RealmId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DatabaseOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        #[Autowire('@db.connection')]
        private Connection $connection,
    ) {}

    public function get(OrganizationId $organizationId, RealmId $realmId): Organization
    {
        $sql =<<<SQL
            SELECT uuid, realm_id, name, slug, valid_until, feature_rollout_ids, enabled
            FROM organizations
            WHERE uuid = :uuid
              AND realm_id = :realm_id
            LIMIT 1
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':uuid', $organizationId->toString(), ParameterType::STRING);
        $statement->bindValue(':realm_id', $realmId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();
        if ($result->rowCount() <= 0) {
            throw new NotFoundException();
        }

        return $this->hydrateOne($result->fetchAssociative());
    }

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): OrganizationPage
    {
        $sql =<<<SQL
            SELECT uuid, realm_id, name, slug, valid_until, feature_rollout_ids, enabled
            FROM organizations
            WHERE realm_id = :realm_id
            LIMIT :limit
            OFFSET :offset
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':limit', $pageSize, ParameterType::INTEGER);
        $statement->bindValue(':offset', $pageSize * ($currentPage - 1), ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $realmId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();
        if ($result->rowCount() <= 0) {
            throw new NotFoundException();
        }

        return new OrganizationPage(1, $pageSize, 0, ...$this->hydrateAll($result));
    }

    private function hydrateOne(array $organization): Organization
    {
        return new Organization(
            OrganizationId::fromString($organization['uuid']),
            realmId: RealmId::fromString($organization['realm_id']),
            name: $organization['name'],
            slug: $organization['slug'],
            validUntil: $organization['valid_until'] !== null
                ? \DateTimeImmutable::createFromFormat('Y-m-d', $organization['valid_until'], new \DateTimeZone('UTC'))
                : null,
            featureRolloutIds: array_map(
                fn (string $featureRolloutId): FeatureRolloutId => FeatureRolloutId::fromString($featureRolloutId),
                json_decode($organization['feature_rollout_ids'], true, JSON_THROW_ON_ERROR)
            ),
            enabled: $organization['enabled'] ?? false,
        );
    }

    private function hydrateAll(Result $result): \Traversable
    {
        foreach ($result->iterateAssociative() as $organization) {
            yield $this->hydrateOne($organization);
        }
    }
}
