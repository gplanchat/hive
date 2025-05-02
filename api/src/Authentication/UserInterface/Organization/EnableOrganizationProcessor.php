<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Organization;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Organization\Command\InvalidOrganizationStateException;
use App\Authentication\Domain\Organization\Command\UseCases\EnableOrganization;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization;
use App\Authentication\Domain\Organization\Query\OrganizationRepositoryInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class EnableOrganizationProcessor implements ProcessorInterface
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []):Organization
    {
        if (!$data instanceof EnableOrganizationInput) {
            throw new BadRequestException();
        }

        try {
            $command = new EnableOrganization(
                OrganizationId::fromString($uriVariables['uuid']),
                $data->validUntil,
            );
            $this->messageBus->dispatch($command);
        } catch (InvalidOrganizationStateException $exception) {
            throw new LogicException($exception->getMessage(), previous: $exception);
        } catch (NotFoundException $exception) {
            throw new LogicException($exception->getMessage(), previous: $exception);
        }

        return $this->organizationRepository->get($command->uuid);
    }
}
