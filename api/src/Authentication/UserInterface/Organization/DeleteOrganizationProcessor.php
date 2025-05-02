<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\InvalidOrganizationStateException;
use App\Authentication\Domain\Organization\Command\UseCases\DeleteOrganization;
use App\Authentication\Domain\Organization\Query\Organization;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class DeleteOrganizationProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof Organization) {
            throw new BadRequestHttpException();
        }

        try {
            $command = new DeleteOrganization($data->uuid);
            $this->messageBus->dispatch($command);
        } catch (InvalidOrganizationStateException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }
    }
}
