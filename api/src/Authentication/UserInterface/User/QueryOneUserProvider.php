<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\User;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\QueryBusInterface;
use App\Authentication\Domain\User\Query\UseCases\QueryOneUser;
use App\Authentication\Domain\User\Query\User;
use App\Authentication\Domain\User\UserId;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class QueryOneUserProvider implements ProviderInterface
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): User
    {
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($uriVariables['uuid'], UserId::class, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = new QueryOneUser(
            UserId::fromString($uriVariables['uuid']),
        );

        try {
            $result = $this->queryBus->query($input);
        } catch (HandlerFailedException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), previous: $exception);
        }

        if (!$result instanceof User) {
            throw new UnprocessableEntityHttpException();
        }

        return $result;
    }
}
