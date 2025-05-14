<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\Query\UseCases\UserPage;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\Infrastructure\Keycloak\KeycloakAuthorization;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class DatabaseUserRepository implements UserRepositoryInterface
{
    public function __construct(
        #[Autowire('@db.connection')]
        private Connection $connection,
    ) {
    }

    public function get(UserId $userId, RealmId $realmId): User
    {
        $sql = <<<'SQL'
            SELECT uuid, realm_id, authorization_context, organization_id, workspace_ids, role_ids, username, firstname, lastname, email, enabled
            FROM users
            WHERE uuid = :uuid
              AND realm_id = :realm_id
            LIMIT 1
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':uuid', $userId->toString(), ParameterType::STRING);
        $statement->bindValue(':realm_id', $realmId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();
        if ($result->rowCount() <= 0) {
            throw new NotFoundException();
        }

        return $this->hydrateOne($result->fetchAssociative());
    }

    public function list(RealmId $realmId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $sql = <<<'SQL'
            SELECT uuid, realm_id, authorization_context, organization_id, workspace_ids, role_ids, username, firstname, lastname, email, enabled
            FROM users
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

        return new UserPage(1, $pageSize, 0, ...$this->hydrateAll($result));
    }

    public function listFromOrganization(RealmId $realmId, OrganizationId $organizationId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $sql = <<<'SQL'
            SELECT uuid, realm_id, authorization_context, organization_id, workspace_ids, role_ids, username, firstname, lastname, email, enabled
            FROM users
            WHERE organization_id = :organization_id
              AND realm_id = :realm_id
            LIMIT :limit
            OFFSET :offset
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':limit', $pageSize, ParameterType::INTEGER);
        $statement->bindValue(':offset', $pageSize * ($currentPage - 1), ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $realmId->toString(), ParameterType::STRING);
        $statement->bindValue(':organization_id', $organizationId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();
        if ($result->rowCount() <= 0) {
            throw new NotFoundException();
        }

        return new UserPage(1, $pageSize, 0, ...$this->hydrateAll($result));
    }

    public function listFromWorkspace(RealmId $realmId, WorkspaceId $workspaceId, int $currentPage = 1, int $pageSize = 25): UserPage
    {
        $sql = <<<'SQL'
            SELECT uuid, realm_id, authorization_context, organization_id, workspace_ids, role_ids, username, firstname, lastname, email, enabled
            FROM users
            WHERE workspace_ids::jsonb ? :workspace_id
              AND realm_id = :realm_id
            LIMIT :limit
            OFFSET :offset
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->bindValue(':limit', $pageSize, ParameterType::INTEGER);
        $statement->bindValue(':offset', $pageSize * ($currentPage - 1), ParameterType::INTEGER);
        $statement->bindValue(':realm_id', $realmId->toString(), ParameterType::STRING);
        $statement->bindValue(':workspace_id', $workspaceId->toString(), ParameterType::STRING);

        $result = $statement->executeQuery();
        if ($result->rowCount() <= 0) {
            throw new NotFoundException();
        }

        return new UserPage(1, $pageSize, 0, ...$this->hydrateAll($result));
    }

    private function hydrateOne(array $user): User
    {
        return new User(
            UserId::fromString($user['uuid']),
            RealmId::fromString($user['realm_id']),
            KeycloakAuthorization::fromNormalized($user['authorization_context']),
            OrganizationId::fromString($user['organization_id']),
            workspaceIds: array_map(
                fn (string $workspaceId): WorkspaceId => WorkspaceId::fromString($workspaceId),
                json_decode($user['workspace_ids'], true, \JSON_THROW_ON_ERROR)
            ),
            roleIds: array_map(
                fn (string $roleId): RoleId => RoleId::fromString($roleId),
                json_decode($user['role_ids'], true, \JSON_THROW_ON_ERROR)
            ),
            username: $user['username'],
            firstName: $user['firstname'],
            lastName: $user['lastname'],
            email: $user['email'],
            enabled: $user['enabled'] ?? false,
        );
    }

    private function hydrateAll(Result $result): \Traversable
    {
        foreach ($result->iterateAssociative() as $user) {
            yield $this->hydrateOne($user);
        }
    }
}
