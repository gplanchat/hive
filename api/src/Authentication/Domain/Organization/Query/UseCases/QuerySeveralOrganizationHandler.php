<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Query\UseCases;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class QuerySeveralOrganizationHandler
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function __invoke(QuerySeveralOrganization $query): organizationPage
    {
        try {
            return $this->organizationRepository->list($query->realmId, $query->currentPage, $query->itemsPerPage);
        } catch (NotFoundException $exception) {
            throw new UnrecoverableMessageHandlingException(previous: $exception);
        }
    }
}
