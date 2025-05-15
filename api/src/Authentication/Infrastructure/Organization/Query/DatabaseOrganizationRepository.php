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
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DatabaseOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        #[Autowire('@db.connection')]
        private Connection $connection,
    ) {
    }

    public function get(OrganizationId $organizationId, RealmId $realmId): Organization
    {
        $sql = <<<'SQL'
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

        $organization = $result->fetchAssociative();
        if (false === $organization) {
            throw new NotFoundException();
        }

        \assert(\array_key_exists('uuid', $organization) && \is_string($organization['uuid']));
        \assert(\array_key_exists('realm_id', $organization) && \is_string($organization['realm_id']));
        \assert(\array_key_exists('name', $organization) && \is_string($organization['name']));
        \assert(\array_key_exists('slug', $organization) && \is_string($organization['slug']));
        \assert(\array_key_exists('valid_until', $organization) && \is_string($organization['valid_until']));
        \assert(\array_key_exists('feature_rollout_ids', $organization) && \is_string($organization['feature_rollout_ids']));
        \assert(\array_key_exists('enabled', $organization) && \is_bool($organization['enabled']));

        return $this->hydrateOne($organization);
    }

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): OrganizationPage
    {
        $sql = <<<'SQL'
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

    /**
     * @param array{
     *     uuid: string,
     *     realm_id: string,
     *     name: string,
     *     slug: string,
     *     valid_until: string|null,
     *     feature_rollout_ids: string,
     *     enabled: bool,
     * } $organization
     */
    private function hydrateOne(array $organization): Organization
    {
        return new Organization(
            OrganizationId::fromString($organization['uuid']),
            realmId: RealmId::fromString($organization['realm_id']),
            name: $organization['name'],
            slug: $organization['slug'],
            validUntil: null !== $organization['valid_until']
                ? \DateTimeImmutable::createFromFormat('Y-m-d', $organization['valid_until'], new \DateTimeZone('UTC')) ?: null
                : null,
            featureRolloutIds: array_map(
                fn (string $featureRolloutId): FeatureRolloutId => FeatureRolloutId::fromString($featureRolloutId),
                json_decode($organization['feature_rollout_ids'], true, \JSON_THROW_ON_ERROR)
            ),
            enabled: $organization['enabled'] ?? false,
        );
    }

    /**
     * @return \Traversable<mixed, Organization>
     *
     * @throws Exception
     */
    private function hydrateAll(Result $result): \Traversable
    {
        foreach ($result->iterateAssociative() as $organization) {
            \assert(\array_key_exists('uuid', $organization) && \is_string($organization['uuid']));
            \assert(\array_key_exists('realm_id', $organization) && \is_string($organization['realm_id']));
            \assert(\array_key_exists('name', $organization) && \is_string($organization['name']));
            \assert(\array_key_exists('slug', $organization) && \is_string($organization['slug']));
            \assert(\array_key_exists('valid_until', $organization) && \is_string($organization['valid_until']));
            \assert(\array_key_exists('feature_rollout_ids', $organization) && \is_string($organization['feature_rollout_ids']));
            \assert(\array_key_exists('enabled', $organization) && \is_bool($organization['enabled']));

            yield $this->hydrateOne($organization);
        }
    }
}
