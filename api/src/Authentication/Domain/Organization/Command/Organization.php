<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Command;

use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\Organization\OrganizationId;
use App\Authentication\Domain\Realm\RealmId;
use App\Shared\Infrastructure\Collection\Collection;
use Webmozart\Assert\Assert;

final class Organization
{
    /**
     * @param FeatureRolloutId[] $featureRolloutIds
     * @param object[] $events
     */
    public function __construct(
        public readonly OrganizationId $uuid,
        public readonly RealmId $realmId,
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
        $methodName = 'apply'.substr($event::class, strrpos($event::class, '\\') + 1);
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
        RealmId $realmId,
        string $name,
        string $slug,
        \DateTimeInterface $validUntil,
        array $featureRolloutIds = [],
    ): self {
        Assert::lengthBetween($name, 3, 255);
        Assert::lengthBetween($slug, 3, 255);

        $instance = new self($uuid, $realmId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $realmId,
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
        RealmId $realmId,
        string $name,
        string $slug,
        array $featureRolloutIds = [],
    ): self {
        $instance = new self($uuid, $realmId);
        $instance->recordThat(new DeclaredEvent(
            $uuid,
            1,
            $realmId,
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

        $this->recordThat(new EnabledEvent($this->uuid, $this->version + 1, $this->realmId, $validUntil));
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

        $this->recordThat(new DisabledEvent($this->uuid, $this->version + 1, $this->realmId, $validUntil));
    }

    private function applyDisabledEvent(DisabledEvent $event): void
    {
        $this->enabled = false;
    }

    public function addFeatureRollouts(FeatureRolloutId ...$featureRolloutIds): void
    {
        if ($this->deleted) {
            throw new InvalidOrganizationStateException('Cannot modify an already deleted Organization.');
        }

        $this->recordThat(new AddedFeatureRolloutsEvent($this->uuid, $this->version + 1, $this->realmId, $featureRolloutIds));
    }

    private function applyAddedFeatureRolloutsEvent(AddedFeatureRolloutsEvent $event): void
    {
        $this->featureRolloutIds = Collection::fromArray([...$this->featureRolloutIds, ...$event->featureRolloutIds])
            ->unique(fn (FeatureRolloutId $left, FeatureRolloutId $right) => $left->equals($right))
            ->toArray();
    }

    public function removeFeatureRollouts(FeatureRolloutId ...$featureRolloutIds): void
    {
        if ($this->deleted) {
            throw new InvalidOrganizationStateException('Cannot modify an already deleted Organization.');
        }

        $this->recordThat(new RemovedFeatureRolloutsEvent($this->uuid, $this->version + 1, $this->realmId, $featureRolloutIds));
    }

    private function applyRemovedFeatureRolloutsEvent(RemovedFeatureRolloutsEvent $event): void
    {
        $this->featureRolloutIds = Collection::fromArray($this->featureRolloutIds)
            ->filter(fn (FeatureRolloutId $current) => Collection::fromArray($event->featureRolloutIds)
                ->none(fn (FeatureRolloutId $toRemove) => $current->equals($toRemove)))
            ->toArray();
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw new InvalidOrganizationStateException('Cannot delete an already deleted Organization.');
        }

        $this->recordThat(new DeletedEvent($this->uuid, $this->version + 1, $this->realmId));
    }

    private function applyDeletedEvent(DeletedEvent $event): void
    {
        $this->enabled = false;
    }
}
