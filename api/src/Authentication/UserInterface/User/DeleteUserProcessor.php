<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\User\Command\InvalidUserStateException;
use App\Authentication\Domain\User\Command\UseCases\DeleteUser;
use App\Authentication\Domain\User\Query\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class DeleteUserProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof User) {
            throw new BadRequestHttpException();
        }

        try {
            $command = new DeleteUser($data->uuid);
            $this->messageBus->dispatch($command);
        } catch (InvalidUserStateException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }
    }
}
