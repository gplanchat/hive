<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command;

use App\Authentication\Domain\EventBusInterface;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\AddedFeatureRolloutsEvent;
use App\Authentication\Domain\Organization\Command\DeclaredEvent;
use App\Authentication\Domain\Organization\Command\DeletedEvent;
use App\Authentication\Domain\Organization\Command\DisabledEvent;
use App\Authentication\Domain\Organization\Command\EnabledEvent;
use App\Authentication\Domain\Organization\Command\Organization;
use App\Authentication\Domain\Organization\Command\OrganizationRepositoryInterface;
use App\Authentication\Domain\Organization\Command\RemovedFeatureRolloutsEvent;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class DatabaseOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        #[Autowire('@db.connection')]
        private Connection $connection,
        private EventBusInterface $eventBus,
    ) {
    }

    public function get(OrganizationId $organizationId, RealmId $realmId): Organization
    {
        $sql = <<<'SQL'
            SELECT uuid, realm_id, name, slug, valid_until, feature_rollout_ids, enabled, version
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

        return new Organization(
            OrganizationId::fromString($organization['uuid']),
            realmId: RealmId::fromString($organization['realm_id']),
            name: $organization['name'],
            validUntil: null !== $organization['valid_until']
                ? \DateTimeImmutable::createFromFormat('Y-m-d', $organization['valid_until'], new \DateTimeZone('UTC'))
                : null,
            featureRolloutIds: array_map(
                fn (string $featureRolloutId): FeatureRolloutId => FeatureRolloutId::fromString($featureRolloutId),
                json_decode($organization['feature_rollout_ids'], true, \JSON_THROW_ON_ERROR)
            ),
            enabled: $organization['enabled'],
            version: $organization['version'],
        );
    }

    public function save(Organization $organization): void
    {
        $this->connection->beginTransaction();
        foreach ($events = $organization->releaseEvents() as $event) {
            try {
                $this->saveEvent($event);
            } catch (\Throwable $exception) {
                $this->connection->rollBack();
                throw $exception;
            }
        }
        $this->connection->commit();

        foreach ($events as $event) {
            $this->eventBus->emit($event);
        }
    }

    private function saveEvent(object $event): void
    {
        $methodName = 'apply'.substr($event::class, strrpos($event::class, '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $statement = $this->connection->prepare(<<<'SQL'
            INSERT INTO organizations (uuid, realm_id, name, valid_until, feature_rollout_ids, enabled, version)
            VALUES (:uuid, :realm_id, :name, :valid_until, :feature_rollout_ids, :enabled, 1)
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':realm_id', $event->realmId->toString(), ParameterType::STRING);
        $statement->bindValue(':name', $event->name, ParameterType::STRING);
        $statement->bindValue(':valid_until', $event->validUntil?->format('Y-m-d'), ParameterType::STRING);
        $statement->bindValue(':feature_rollout_ids', json_encode(
            array_map(fn (FeatureRolloutId $featureRolloutId) => $featureRolloutId->toString(), $event->featureRolloutIds),
            \JSON_THROW_ON_ERROR,
        ), ParameterType::STRING);
        $statement->bindValue(':enabled', $event->enabled, ParameterType::BOOLEAN);

        $result = $statement->executeQuery();

        if (1 !== $result->rowCount()) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $statement = $this->connection->prepare(<<<'SQL'
            UPDATE organizations
            SET enabled = true,
                version = :version
            WHERE uuid = :uuid
              AND version=(:version - 1)
              AND realm_id = :realm_id
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $event->realmId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();

        if (1 !== $result->rowCount()) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $statement = $this->connection->prepare(<<<'SQL'
            UPDATE organizations
            SET enabled = false,
                version = :version
            WHERE uuid = :uuid
              AND version=(:version - 1)
              AND realm_id = :realm_id
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $event->realmId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();

        if (1 !== $result->rowCount()) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }

    private function applyAddedFeatureRolloutEvent(AddedFeatureRolloutsEvent $event): void
    {
        $statement = $this->connection->prepare(<<<'SQL'
            UPDATE organizations
            SET feature_rollout_ids = :feature_rollout_ids,
                version = :version
            WHERE uuid = :uuid
              AND version=(:version - 1)
              AND realm_id = :realm_id
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $event->realmId->toString(), ParameterType::STRING);
        $statement->bindValue(':feature_rollout_ids', json_encode(
            array_map(fn (FeatureRolloutId $featureRolloutId) => $featureRolloutId->toString(), $event->featureRolloutIds),
            \JSON_THROW_ON_ERROR,
        ), ParameterType::STRING);
    }

    private function applyRemovedFeatureRolloutEvent(RemovedFeatureRolloutsEvent $event): void
    {
        $statement = $this->connection->prepare(<<<'SQL'
            UPDATE organizations
            SET feature_rollout_ids = :feature_rollout_ids,
                version = :version
            WHERE uuid = :uuid
              AND version=(:version - 1)
              AND realm_id = :realm_id
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $event->realmId->toString(), ParameterType::STRING);
        $statement->bindValue(':feature_rollout_ids', json_encode(
            array_map(fn (FeatureRolloutId $featureRolloutId) => $featureRolloutId->toString(), $event->featureRolloutIds),
            \JSON_THROW_ON_ERROR,
        ), ParameterType::STRING);
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $statement = $this->connection->prepare(<<<'SQL'
            DELETE FROM organizations
            WHERE uuid = :uuid
              AND version=(:version - 1)
              AND realm_id = :realm_id
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $event->realmId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();

        if (1 !== $result->rowCount()) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }
}
