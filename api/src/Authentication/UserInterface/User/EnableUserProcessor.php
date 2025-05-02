<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\User\Command\InvalidUserStateException;
use App\Authentication\Domain\User\Command\UseCases\EnableUser;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\Query\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EnableUserProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []):User
    {
        if (!$data instanceof EnableUserInput) {
            throw new BadRequestException();
        }

        try {
            $command = new EnableUser(
                UserId::fromString($uriVariables['uuid']),
            );
            $this->messageBus->dispatch($command);
        } catch (InvalidUserStateException $exception) {
            throw new LogicException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new LogicException($exception->getMessage(), previous: $exception);
        }

        return $this->userRepository->get($command->uuid);
    }
}
