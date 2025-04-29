<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace;

use App\IdInterface;
use App\InvalidUuidFormatException;
use Symfony\Component\Routing\Requirement\Requirement;

final class WorkspaceId implements IdInterface
{
    const string REQUIREMENT = '\/authentication\/workspaces\/'.Requirement::UUID_V7;

    private function __construct(
        private readonly string $reference,
    ) {
        if (!uuid_is_valid($this->reference)) {
            throw new InvalidUuidFormatException(\sprintf('<%s> is not a valid UUID.', $reference));
        }
    }

    public static function generateRandom(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    public static function nil(): IdInterface
    {
        return new self(uuid_create(UUID_TYPE_NULL));
    }

    public static function fromString(string $reference): self
    {
        return new self($reference);
    }

    public function equals(IdInterface|string $other): bool
    {
        if (\is_string($other)) {
            return 0 === uuid_compare($this->reference, $other);
        }

        if (!$other instanceof self) {
            return false;
        }

        return 0 === uuid_compare($this->reference, $other->reference);
    }

    public function isNil(): bool
    {
        return uuid_is_null($this->reference);
    }

    public function __toString(): string
    {
        return $this->reference;
    }

    public function toString(): string
    {
        return $this->reference;
    }
}
