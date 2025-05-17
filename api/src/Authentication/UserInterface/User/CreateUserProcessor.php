<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\SecurityContextInterface;
use App\Authentication\Domain\User\Command\UseCases\CreateEnabledUser;
use App\Authentication\Domain\User\Command\UseCases\CreatePendingUser;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use App\Platform\Infrastructure\CommandBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<CreateUserInput|CreateUserWithinOrganizationInput, User>
 */
final readonly class CreateUserProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private UserRepositoryInterface $userRepository,
        private SecurityContextInterface $securityContext,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        try {
            $realmId = RealmId::fromString($uriVariables['realm']);

            if ($data instanceof CreateUserWithinOrganizationInput
                && \array_key_exists('organizationId', $uriVariables)
            ) {
                $organizationId = OrganizationId::fromString($uriVariables['organizationId']);

                $command = $data->enabled
                    ? new CreateEnabledUser(
                        uuid: UserId::generateRandom(),
                        realmId: $realmId,
                        username: $data->username,
                        authorization: $this->securityContext->authorization(),
                        organizationId: $organizationId,
                        workspaceIds: $data->workspaceIds,
                        roleIds: $data->roleIds,
                        firstName: $data->firstName,
                        lastName: $data->lastName,
                        email: $data->email,
                    )
                    : new CreatePendingUser(
                        uuid: UserId::generateRandom(),
                        realmId: $realmId,
                        username: $data->username,
                        authorization: $this->securityContext->authorization(),
                        organizationId: $organizationId,
                        workspaceIds: $data->workspaceIds,
                        roleIds: $data->roleIds,
                        firstName: $data->firstName,
                        lastName: $data->lastName,
                        email: $data->email,
                    );
            } elseif ($data instanceof CreateUserInput) {
                $command = $data->enabled
                    ? new CreateEnabledUser(
                        uuid: UserId::generateRandom(),
                        realmId: $realmId,
                        username: $data->username,
                        authorization: $this->securityContext->authorization(),
                        organizationId: $data->organizationId,
                        workspaceIds: $data->workspaceIds,
                        roleIds: $data->roleIds,
                        firstName: $data->firstName,
                        lastName: $data->lastName,
                        email: $data->email,
                    )
                    : new CreatePendingUser(
                        uuid: UserId::generateRandom(),
                        realmId: $realmId,
                        username: $data->username,
                        authorization: $this->securityContext->authorization(),
                        organizationId: $data->organizationId,
                        workspaceIds: $data->workspaceIds,
                        roleIds: $data->roleIds,
                        firstName: $data->firstName,
                        lastName: $data->lastName,
                        email: $data->email,
                    );
            } else {
                throw new BadRequestHttpException();
            }

            $this->commandBus->apply($command);
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        return $this->userRepository->get($command->uuid, $realmId);
    }
}
