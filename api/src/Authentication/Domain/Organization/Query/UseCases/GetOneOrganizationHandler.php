<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Query\UseCases;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final readonly class GetOneOrganizationHandler
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function __invoke(GetOneOrganization $query): Organization
    {
        try {
            return $this->organizationRepository->get($query->uuid);
        } catch (NotFoundException $exception) {
            throw new UnrecoverableMessageHandlingException(previous: $exception);
        }
    }
}
