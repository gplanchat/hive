<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command\UseCases;

use App\Authentication\Domain\Organization\Command\OrganizationRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class EnableOrganizationHandler
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function __invoke(EnableOrganization $command): void
    {
        $organization = $this->organizationRepository->get($command->uuid);
        $organization->enable($command->validUntil);
        $this->organizationRepository->save($organization);
    }
}
