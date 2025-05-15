<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\User\Command\InvalidUserStateException;
use App\Authentication\Domain\User\Command\UseCases\DisableUser;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use App\Authentication\Domain\User\UserId;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<DisableUserInput, User>
 */
final readonly class DisableUserProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): User
    {
        if (!$data instanceof DisableUserInput) {
            throw new BadRequestHttpException();
        }

        try {
            $command = new DisableUser(
                UserId::fromString($uriVariables['uuid']),
                RealmId::fromString($uriVariables['realm']),
            );
            $this->commandBus->apply($command);
        } catch (InvalidUserStateException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        return $this->userRepository->get($command->uuid, $command->realmId);
    }
}
