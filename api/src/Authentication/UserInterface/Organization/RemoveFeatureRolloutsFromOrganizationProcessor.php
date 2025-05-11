<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\CommandBusInterface;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\InvalidOrganizationStateException;
use App\Authentication\Domain\Organization\Command\UseCases\RemoveFeatureRolloutsFromOrganization;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use App\Authentication\Domain\Realm\RealmId;
use App\Shared\Infrastructure\Collection\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class RemoveFeatureRolloutsFromOrganizationProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Organization
    {
        if (!$data instanceof RemoveFeatureRolloutsFromOrganizationInput) {
            throw new BadRequestHttpException();
        }

        try {
            $command = new RemoveFeatureRolloutsFromOrganization(
                OrganizationId::fromString($uriVariables['uuid']),
                RealmId::fromString($uriVariables['realm']),
                Collection::fromArray($data->featureRolloutIds)
                    ->unique(fn (FeatureRolloutId $left, FeatureRolloutId $right) => $left->equals($right))
                    ->toArray(),
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
