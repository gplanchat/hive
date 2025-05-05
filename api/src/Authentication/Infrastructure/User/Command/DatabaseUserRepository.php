<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command;

use App\Authentication\Domain\EventBusInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\Command\DeclaredEvent;
use App\Authentication\Domain\User\Command\DeletedEvent;
use App\Authentication\Domain\User\Command\DisabledEvent;
use App\Authentication\Domain\User\Command\EnabledEvent;
use App\Authentication\Domain\User\Command\User;
use App\Authentication\Domain\User\Command\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DatabaseUserRepository implements UserRepositoryInterface
{
    public function __construct(
        #[Autowire('@db.connection')]
        private Connection $connection,
        private EventBusInterface $eventBus,
    ) {}

    public function get(UserId $userId): User
    {
        $sql = <<<SQL
            SELECT uuid, organization_id, workspace_ids, role_ids, username, firstname, lastname, email, enabled, version
            FROM users
            WHERE uuid = :uuid
            LIMIT 1
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':uuid', $userId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();
        if ($result->rowCount() <= 0) {
            throw new NotFoundException();
        }

        $user = $result->fetchAssociative();

        return new User(
            UserId::fromString($user['uuid']),
            OrganizationId::fromString($user['organization_id']),
            workspaceIds: array_map(
                fn (string $workspaceIds): WorkspaceId => WorkspaceId::fromString($workspaceIds),
                json_decode($user['workspace_ids'], true, JSON_THROW_ON_ERROR)
            ),
            roleIds: array_map(
                fn (string $roleId): RoleId => RoleId::fromString($roleId),
                json_decode($user['role_ids'], true, JSON_THROW_ON_ERROR)
            ),
            username: $user['username'],
            firstName: $user['firstname'],
            lastName: $user['lastname'],
            email: $user['email'],
            enabled: $user['enabled'],
            version: $user['version'],
        );
    }

    public function save(User $user): void
    {
        $this->connection->beginTransaction();
        foreach ($events = $user->releaseEvents() as $event) {
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
        $methodName = 'apply'.substr(get_class($event), strrpos(get_class($event), '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $statement = $this->connection->prepare(<<<SQL
            INSERT INTO users (uuid, organization_id, workspace_ids, role_ids, username, firstname, lastname, email, enabled, version)
            VALUES (:uuid, :organization_id, :workspace_ids, :role_ids, :username, :firstname, :lastname, :email, :enabled, 1)
            SQL
        );

        $statement->bindValue(':uuid', $event->uuid->toString(), ParameterType::STRING);
        $statement->bindValue(':organization_id', $event->organizationId->toString(), ParameterType::STRING);
        $statement->bindValue(':workspace_ids', json_encode(
            array_map(fn (WorkspaceId $workspaceId) => $workspaceId->toString(), $event->workspaceIds),
            JSON_THROW_ON_ERROR,
        ), ParameterType::STRING);
        $statement->bindValue(':role_ids', json_encode(
            array_map(fn (RoleId $roleId) => $roleId->toString(), $event->roleIds),
            JSON_THROW_ON_ERROR,
        ), ParameterType::STRING);
        $statement->bindValue(':username', $event->username, ParameterType::STRING);
        $statement->bindValue(':firstname', $event->firstName, ParameterType::STRING);
        $statement->bindValue(':lastname', $event->lastName, ParameterType::STRING);
        $statement->bindValue(':email', $event->email, ParameterType::STRING);
        $statement->bindValue(':enabled', $event->enabled, ParameterType::BOOLEAN);

        $result = $statement->executeQuery();

        if ($result->rowCount() !== 1) {
            throw new \RuntimeException('Version mismatch. This happens in case of concurrency between several processes.');
        }
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $statement = $this->connection->prepare(<<<SQL
            UPDATE users
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
            UPDATE users
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
            DELETE FROM users
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
