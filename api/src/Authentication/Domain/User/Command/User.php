<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Role\RoleId;
use App\Authentication\Domain\User\Query\User as QueryUser;
use App\Authentication\Domain\User\UserId;
use App\Authentication\Domain\Workspace\WorkspaceId;
use App\Authentication\UserInterface\User\CreateUserWithinOrganizationInput;
use App\Authentication\UserInterface\User\CreateUserInput;
use App\Authentication\UserInterface\User\CreateUserProcessor;
use App\Authentication\UserInterface\User\DeleteUserProcessor;
use App\Authentication\UserInterface\User\DisableUserInput;
use App\Authentication\UserInterface\User\DisableUserProcessor;
use App\Authentication\UserInterface\User\EnableUserInput;
use App\Authentication\UserInterface\User\EnableUserProcessor;
use App\Authentication\UserInterface\User\GetOneUserProvider;

#[Post(
    uriTemplate: '/authentication/organizations/{organizationId}/users',
    input: CreateUserWithinOrganizationInput::class,
    output: QueryUser::class,
    processor: CreateUserProcessor::class,
)]
#[Post(
    uriTemplate: '/authentication/users',
    input: CreateUserInput::class,
    output: QueryUser::class,
    processor: CreateUserProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/users/{uuid}/enable',
    uriVariables: ['uuid'],
    input: EnableUserInput::class,
    output: QueryUser::class,
    provider: GetOneUserProvider::class,
    processor: EnableUserProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/users/{uuid}/disable',
    uriVariables: ['uuid'],
    input: DisableUserInput::class,
    output: QueryUser::class,
    provider: GetOneUserProvider::class,
    processor: DisableUserProcessor::class,
)]
#[Delete(
    uriTemplate: '/authentication/users/{uuid}',
    uriVariables: ['uuid'],
    input: false,
    output: false,
    provider: GetOneUserProvider::class,
    processor: DeleteUserProcessor::class,
)]
final class User
{
    /**
     * @param WorkspaceId[] $workspaceIds
     * @param RoleId[] $roleIds
     * @param object[] $events
     */
    public function __construct(
        public readonly UserId $uuid,
        private readonly OrganizationId $organizationId,
        private array $workspaceIds = [],
        private array $roleIds = [],
        private ?string $username = null,
        private ?string $firstName = null,
        private ?string $lastName = null,
        private ?string $email = null,
        private bool $enabled = true,
        private bool $deleted = false,
        private array $events = [],
        private int $version = 0,
    ) {
    }

    private function apply(object $event): void
    {
        $methodName = 'apply'.substr(__CLASS__, strrpos(__CLASS__, '\\') + 1);
        if (method_exists($this, $methodName)) {
            $this->{$methodName}($event);
        }
    }

    private function recordThat(object $event): void
    {
        $this->events[] = $event;
        $this->version++;
        $this->apply($event);
    }

    public function releaseEvents(): array
    {
        $releasedEvents = $this->events;
        $this->events = [];
        return $releasedEvents;
    }

    /**
     * @param WorkspaceId[] $workspaceIds
     * @param RoleId[] $roleIds
     */
    public static function declareEnabled(
        UserId $uuid,
        OrganizationId $organizationId,
        array $workspaceIds,
        array $roleIds,
        string $username,
        string $firstName,
        string $lastName,
        string $email,
    ): self {
        $instance = new self($uuid, $organizationId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $organizationId,
            $workspaceIds,
            $roleIds,
            $username,
            $firstName,
            $lastName,
            $email,
            true,
        ));

        return $instance;
    }

    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public static function declareDisabled(
        UserId $uuid,
        OrganizationId $organizationId,
        array $workspaceIds,
        array $roleIds,
        string $username,
        string $firstName,
        string $lastName,
        string $email,
    ): self {
        $instance = new self($uuid, $organizationId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $organizationId,
            $workspaceIds,
            $roleIds,
            $username,
            $firstName,
            $lastName,
            $email,
            false,
        ));

        return $instance;
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->workspaceIds = $event->workspaceIds;
        $this->roleIds = $event->roleIds;
        $this->firstName = $event->firstName;
        $this->lastName = $event->lastName;
        $this->email = $event->email;
        $this->enabled = $event->enabled;
    }

    public function enable(): void
    {
        if ($this->deleted) {
            throw new InvalidUserStateException('Cannot enable an already deleted Organization.');
        }
        if ($this->enabled) {
            throw new InvalidUserStateException('Cannot enable an already enabled Organization.');
        }

        $this->recordThat(new EnabledEvent($this->uuid, $this->version + 1));
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        if ($this->deleted) {
            throw new InvalidUserStateException('Cannot disable an already deleted Organization.');
        }
        if (!$this->enabled) {
            throw new InvalidUserStateException('Cannot disable an already disabled Organization.');
        }

        $this->recordThat(new DisabledEvent($this->uuid, $this->version + 1));
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $this->enabled = false;
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw new InvalidUserStateException('Cannot delete an already deleted Organization.');
        }

        $this->recordThat(new DeletedEvent($this->uuid, $this->version + 1));
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $this->enabled = false;
    }
}
