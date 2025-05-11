<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\InvalidOrganizationStateException;
use App\Authentication\Domain\Organization\Command\UseCases\DisableOrganization;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\Realm\RealmId;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class DisableOrganizationProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Organization
    {
        if (!$data instanceof DisableOrganizationInput) {
            throw new BadRequestHttpException();
        }

        try {
            $command = new DisableOrganization(
                OrganizationId::fromString($uriVariables['uuid']),
                RealmId::fromString($uriVariables['realm']),
                $data->validUntil,
            );
            $this->commandBus->apply($command);
        } catch (InvalidOrganizationStateException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        return $this->organizationRepository->get($command->uuid, $command->realmId);
    }
}
