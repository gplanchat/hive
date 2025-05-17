<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Role;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Role\Command\InvalidRoleStateException;
use App\Authentication\Domain\Role\Command\UseCases\DeleteRole;
use App\Authentication\Domain\Role\Query\Role;
use App\Platform\Infrastructure\CommandBusInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<Role, void>
 */
final readonly class DeleteRoleProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if (!$data instanceof Role) {
            throw new BadRequestHttpException();
        }

        try {
            $command = new DeleteRole($data->uuid, $data->realmId);
            $this->commandBus->apply($command);
        } catch (InvalidRoleStateException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }
    }
}
