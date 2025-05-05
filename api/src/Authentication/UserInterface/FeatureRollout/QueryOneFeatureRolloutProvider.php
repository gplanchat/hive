<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\FeatureRollout;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Authentication\Domain\QueryBusInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class QueryOneFeatureRolloutProvider implements ProviderInterface
{
    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): FeatureRollout
    {
        $type = $operation->getInput()['class'];
        $request = $context['request'];
        if (!$this->denormalizer->supportsDenormalization($context, $type, $request->getRequestFormat())) {
            throw new BadRequestException();
        }

        $input = $this->denormalizer->denormalize($context, $type, $request->getRequestFormat());

        $result = $this->queryBus->query($input);

        if (!$result instanceof FeatureRollout) {
            throw new LogicException();
        }

        return $result;
    }
}
