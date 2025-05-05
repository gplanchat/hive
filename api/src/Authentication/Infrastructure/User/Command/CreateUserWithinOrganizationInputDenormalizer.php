<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\User\Command;

use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\User\CreateUserInput;
use App\Authentication\UserInterface\User\CreateUserWithinOrganizationInput;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[AutoconfigureTag('serializer.normalizer')]
final class CreateUserWithinOrganizationInputDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function getSupportedTypes(?string $format): array
    {
        return in_array($format, ['json', 'jsonld'], true) ? [
            CreateUserWithinOrganizationInput::class => false,
        ] : [];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): CreateUserWithinOrganizationInput
    {
        if (!array_key_exists('username', $data) || !is_string($data['username'])
            || !array_key_exists('firstName', $data) || !is_string($data['firstName'])
            || !array_key_exists('lastName', $data) || !is_string($data['lastName'])
            || !array_key_exists('email', $data) || !is_string($data['email'])
        ) {
            throw new UnexpectedValueException();
        }

        return new CreateUserWithinOrganizationInput(
            workspaceIds: array_map(fn (string $current) => WorkspaceId::fromUri($current), $data['workspaceIds']),
            roleIds: array_map(fn (string $current) => RoleId::fromUri($current), $data['roleIds']),
            username: $data['username'],
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'],
            enabled: (bool) ($data['enabled'] ?? false),
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === CreateUserWithinOrganizationInput::class;
    }
}
