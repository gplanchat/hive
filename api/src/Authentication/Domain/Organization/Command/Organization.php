<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Organization\Query\Organization as QueryOrganization;
use App\Authentication\UserInterface\Organization\CreateOrganizationInput;
use App\Authentication\UserInterface\Organization\CreateOrganizationProcessor;
use App\Authentication\UserInterface\Organization\DeleteOrganizationProcessor;
use App\Authentication\UserInterface\Organization\DisableOrganizationInput;
use App\Authentication\UserInterface\Organization\DisableOrganizationProcessor;
use App\Authentication\UserInterface\Organization\EnableOrganizationInput;
use App\Authentication\UserInterface\Organization\EnableOrganizationProcessor;
use App\Authentication\UserInterface\Organization\QueryOneOrganizationProvider;

#[Post(
    uriTemplate: '/authentication/organizations',
    class: QueryOrganization::class,
    input: CreateOrganizationInput::class,
    output: QueryOrganization::class,
    processor: CreateOrganizationProcessor::class,
    itemUriTemplate: '/authentication/organizations/{uuid}',
)]
#[Patch(
    uriTemplate: '/authentication/organizations/{uuid}/enable',
    uriVariables: ['uuid'],
    class: QueryOrganization::class,
    input: EnableOrganizationInput::class,
    output: QueryOrganization::class,
    provider: QueryOneOrganizationProvider::class,
    processor: EnableOrganizationProcessor::class,
)]
#[Patch(
    uriTemplate: '/authentication/organizations/{uuid}/disable',
    uriVariables: ['uuid'],
    class: QueryOrganization::class,
    input: DisableOrganizationInput::class,
    output: QueryOrganization::class,
    provider: QueryOneOrganizationProvider::class,
    processor: DisableOrganizationProcessor::class,
)]
#[Delete(
    uriTemplate: '/authentication/organizations/{uuid}',
    uriVariables: ['uuid'],
    class: QueryOrganization::class,
    input: false,
    output: false,
    provider: QueryOneOrganizationProvider::class,
    processor: DeleteOrganizationProcessor::class,
)]
final class Organization
{
    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     * @param object[] $events
     */
    public function __construct(
        public readonly OrganizationId $uuid,
        private ?string $name = null,
        private ?string $slug = null,
        private ?\DateTimeInterface $validUntil = null,
        private array $featureRolloutIds = [],
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
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public static function declareEnabled(
        OrganizationId $uuid,
        string $name,
        string $slug,
        \DateTimeInterface $validUntil,
        array $featureRolloutIds = [],
    ): self {
        $instance = new self($uuid);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $name,
            $slug,
            $validUntil,
            $featureRolloutIds,
            true,
        ));

        return $instance;
    }

    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public static function declareDisabled(
        OrganizationId $uuid,
        string $name,
        string $slug,
        array $featureRolloutIds = [],
    ): self {
        $instance = new self($uuid);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $name,
            $slug,
            null,
            $featureRolloutIds,
            false,
        ));

        return $instance;
    }

    private function applyDeclaredEvent(DeclaredEvent $event): void
    {
        $this->name = $event->name;
        $this->slug = $event->slug;
        $this->validUntil = $event->validUntil;
        $this->featureRolloutIds = $event->featureRolloutIds;
        $this->enabled = $event->enabled;
    }

    public function enable(?\DateTimeInterface $validUntil = null): void
    {
        if ($this->deleted) {
            throw new InvalidOrganizationStateException('Cannot enable an already deleted Organization.');
        }
        if ($this->enabled) {
            throw new InvalidOrganizationStateException('Cannot enable an already enabled Organization.');
        }

        $this->recordThat(new EnabledEvent($this->uuid, $this->version + 1, $validUntil));
    }

    private function applyEnabledEvent(EnabledEvent $event): void
    {
        $this->enabled = true;
    }

    public function disable(?\DateTimeInterface $validUntil = null): void
    {
        if ($this->deleted) {
            throw new InvalidOrganizationStateException('Cannot disable an already deleted Organization.');
        }
        if (!$this->enabled) {
            throw new InvalidOrganizationStateException('Cannot disable an already disabled Organization.');
        }

        $this->recordThat(new DisabledEvent($this->uuid, $this->version + 1, $validUntil));
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $this->enabled = false;
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw new InvalidOrganizationStateException('Cannot delete an already deleted Organization.');
        }

        $this->recordThat(new DeletedEvent($this->uuid, $this->version + 1));
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $this->enabled = false;
    }
}
