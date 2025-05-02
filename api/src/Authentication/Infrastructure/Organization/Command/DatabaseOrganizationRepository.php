<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Organization\Command;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\DeclaredEvent;
use App\Authentication\Domain\Organization\Command\DeletedEvent;
use App\Authentication\Domain\Organization\Command\DisabledEvent;
use App\Authentication\Domain\Organization\Command\EnabledEvent;
use App\Authentication\Domain\Organization\Command\Organization;
use App\Authentication\Domain\Organization\Command\OrganizationRepositoryInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

final class DatabaseOrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(
        #[Autowire('@db.connection')]
        private Connection $connection,
        private MessageBusInterface $messageBus,
    ) {}

    public function get(OrganizationId $organizationId): Organization
    {
        $sql =<<<SQL
            SELECT uuid, name, slug, valid_until, feature_rollout_ids, enabled, version
            FROM organizations
            WHERE uuid = :uuid
            LIMIT 1
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':uuid', $organizationId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();
        if ($result->rowCount() <= 0) {
            throw new NotFoundException();
        }

        $organization = $result->fetchAssociative();

        return new Organization(
            OrganizationId::fromString($organization['uuid']),
            name: $organization['name'],
            validUntil: $organization['valid_until'] !== null
                ? \DateTimeImmutable::createFromFormat('Y-m-d', $organization['valid_until'], new \DateTimeZone('UTC'))
                : null,
            featureRolloutIds: array_map(
                fn (string $featureRolloutId): FeatureRolloutId => FeatureRolloutId::fromString($featureRolloutId),
                json_decode($organization['feature_rollout_ids'], true, JSON_THROW_ON_ERROR)
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
            $this->messageBus->dispatch($event);
        }
    }

    private function saveEvent(object $event): void
    {
        $methodName = 'apply'.substr(get_class($event), strrpos(get_class($event), '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $statement = $this->connection->prepare(<<<SQL
            INSERT INTO organizations (uuid, name, valid_until, feature_rollout_ids, enabled, version)
            VALUES (:uuid, :name, :valid_until, :feature_rollout_ids, :enabled, 1)
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':name', $event->name, ParameterType::STRING);
        $statement->bindValue(':valid_until', $event->validUntil?->format('Y-m-d'), ParameterType::STRING);
        $statement->bindValue(':feature_rollout_ids', json_encode(
            array_map(fn (FeatureRolloutId $featureRolloutId) => $featureRolloutId->toString(), $event->featureRolloutIds),
            JSON_THROW_ON_ERROR,
        ), ParameterType::STRING);
        $statement->bindValue(':enabled', $event->enabled, ParameterType::BOOLEAN);

        $result = $statement->executeQuery();

        if ($result->rowCount() !== 1) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $statement = $this->connection->prepare(<<<SQL
            UPDATE organizations
            SET enabled = true,
                version = :version
            WHERE uuid = :uuid AND version=(:version - 1)
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);

        $result = $statement->executeQuery();

        if ($result->rowCount() !== 1) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $statement = $this->connection->prepare(<<<SQL
            UPDATE organizations
            SET enabled = false,
                version = :version
            WHERE uuid = :uuid AND version=(:version - 1)
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);

        $result = $statement->executeQuery();

        if ($result->rowCount() !== 1) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $statement = $this->connection->prepare(<<<SQL
            DELETE FROM organizations
            WHERE uuid = :uuid AND version=(:version - 1)
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':version', $event->version, ParameterType::INTEGER);

        $result = $statement->executeQuery();

        if ($result->rowCount() !== 1) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }
}
