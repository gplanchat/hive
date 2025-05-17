<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command\UseCases;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutRepositoryInterface;
use App\Authentication\Domain\FeatureRollout\Targets;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\OrganizationRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class AddFeatureRolloutsToOrganizationHandler
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private FeatureRolloutRepositoryInterface $featureRolloutRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(AddFeatureRolloutsToOrganization $command): void
    {
        $organization = $this->organizationRepository->get($command->uuid, $command->realmId);

        $featureRolloutIds = [];
        foreach ($command->featureRolloutIds as $featureRolloutId) {
            try {
                $featureRollout = $this->featureRolloutRepository->get($featureRolloutId);
            } catch (NotFoundException) {
                $this->logger->warning(strtr(
                    'The %featureRolloutId% does not exist.',
                    [
                        '%featureRolloutId%' => $featureRolloutId->toString(),
                    ]
                ));
                continue;
            }

            if (!\in_array(Targets::Organization, $featureRollout->targets)) {
                $this->logger->warning(strtr(
                    'The %featureRolloutId% is not applicable to an organization.',
                    [
                        '%featureRolloutId%' => $featureRolloutId->toString(),
                    ]
                ));
            }

            $featureRolloutIds[] = $featureRolloutId;
        }

        $organization->addFeatureRollouts(...$featureRolloutIds);
        $this->organizationRepository->save($organization);
    }
}
