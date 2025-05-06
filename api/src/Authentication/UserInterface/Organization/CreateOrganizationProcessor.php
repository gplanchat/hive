<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\Organization\Command\UseCases\CreateEnabledOrganization;
use App\Authentication\Domain\Organization\Command\UseCases\CreatePendingOrganization;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\Realm\RealmId;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CreateOrganizationProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Organization
    {
        if (!$data instanceof CreateOrganizationInput) {
            throw new BadRequestHttpException();
        }

        try {
            $command = $data->enabled
                ? new CreateEnabledOrganization(
                    OrganizationId::generateRandom(),
                    RealmId::fromString($uriVariables['realm']),
                    $data->name,
                    $data->slug,
                    $data->validUntil,
                    $data->featureRolloutIds,
                )
                : new CreatePendingOrganization(
                    OrganizationId::generateRandom(),
                    RealmId::fromString($uriVariables['realm']),
                    $data->name,
                    $data->slug,
                    $data->featureRolloutIds,
                );
            $this->commandBus->apply($command);
        } catch (NotFoundHttpException $exception) {
            throw new LogicException($exception->getMessage(), previous: $exception);
        }

        return $this->organizationRepository->get($command->uuid);
    }
}
