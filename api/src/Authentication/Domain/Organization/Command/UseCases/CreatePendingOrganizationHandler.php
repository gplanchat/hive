<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command\UseCases;

use App\Authentication\Domain\Organization\Command\Organization;
use App\Authentication\Domain\Organization\Command\OrganizationRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreatePendingOrganizationHandler
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }
    public function __invoke(CreatePendingOrganization $command): void
    {
        $organization = Organization::declareDisabled(
            $command->uuid,
            $command->realmId,
            $command->name,
            $command->slug,
            $command->featureRolloutIds,
        );

        $this->organizationRepository->save($organization);
    }
}
