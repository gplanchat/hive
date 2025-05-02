<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\User\Command\UseCases\CreateEnabledUser;
use App\Authentication\Domain\User\Command\UseCases\CreatePendingUser;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CreateUserProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if (!$data instanceof CreateUserInput) {
            throw new BadRequestHttpException();
        }

        try {
            if ($data instanceof CreateUserWithinOrganizationInput
                && array_key_exists('organizationId', $uriVariables)
            ) {
                $organizationId = OrganizationId::fromString($uriVariables['organizationId']);

                $command = $data->enabled
                    ? new CreateEnabledUser(
                        UserId::generateRandom(),
                        $organizationId,
                        $data->workspaceIds,
                        $data->roleIds,
                        $data->identifier,
                        $data->firstName,
                        $data->lastName,
                        $data->email,
                    )
                    : new CreatePendingUser(
                        UserId::generateRandom(),
                        $organizationId,
                        $data->workspaceIds,
                        $data->roleIds,
                        $data->identifier,
                        $data->firstName,
                        $data->lastName,
                        $data->email,
                    );
            } else if ($data instanceof CreateUserInput) {
                $command = $data->enabled
                    ? new CreateEnabledUser(
                        UserId::generateRandom(),
                        $data->organizationId,
                        $data->workspaceIds,
                        $data->roleIds,
                        $data->identifier,
                        $data->firstName,
                        $data->lastName,
                        $data->email,
                    )
                    : new CreatePendingUser(
                        UserId::generateRandom(),
                        $data->organizationId,
                        $data->workspaceIds,
                        $data->roleIds,
                        $data->identifier,
                        $data->firstName,
                        $data->lastName,
                        $data->email,
                    );
            } else {
                throw new BadRequestHttpException();
            }

            $this->messageBus->dispatch($command);
        } catch (NotFoundHttpException $exception) {
            throw new LogicException($exception->getMessage(), previous: $exception);
        }

        return $this->userRepository->get($command->uuid);
    }
}
