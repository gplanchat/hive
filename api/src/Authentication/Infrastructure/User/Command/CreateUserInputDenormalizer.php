<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\User\CreateUserInput;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class CreateUserInputDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return \in_array($format, ['json', 'jsonld'], true) ? [
            CreateUserInput::class => false,
        ] : [];
    }

    /**
     * @param array{} $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): CreateUserInput
    {
        if (!\array_key_exists('username', $data) || !\is_string($data['username'])
            || !\array_key_exists('firstName', $data) || !\is_string($data['firstName'])
            || !\array_key_exists('lastName', $data) || !\is_string($data['lastName'])
            || !\array_key_exists('email', $data) || !\is_string($data['email'])
            || !\array_key_exists('organizationId', $data) || !\is_string($data['organizationId'])
        ) {
            throw new UnexpectedValueException();
        }

        return new CreateUserInput(
            organizationId: \strlen($data['organizationId']) > 0
                ? OrganizationId::fromUri($data['organizationId'])
                : throw new UnexpectedValueException('Organization id can\'t be empty'),
            username: $data['username'],
            workspaceIds: array_map(
                fn (string $current) => \strlen($current) > 0
                    ? WorkspaceId::fromUri($current)
                    : throw new \UnexpectedValueException('Workspace id can\'t be empty'),
                $data['workspaceIds']
            ),
            roleIds: array_map(
                fn (string $current) => \strlen($current) > 0
                    ? RoleId::fromUri($current)
                    : throw new \UnexpectedValueException('Role id can\'t be empty'),
                $data['roleIds']
            ),
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'],
            enabled: (bool) ($data['enabled'] ?? false),
        );
    }

    /**
     * @param array{} $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return CreateUserInput::class === $type;
    }
}
