<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Platform\Domain\InvalidUuidFormatException;

final class KeycloakUserId
{
    /**
     * @param non-empty-string $reference
     */
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

    public static function nil(): self
    {
        return new self(uuid_create(UUID_TYPE_NULL));
    }

    /**
     * @param non-empty-string $reference
     */
    public static function fromString(string $reference): self
    {
        return new self($reference);
    }

    /**
     * @param self|non-empty-string $other
     */
    public function equals(self|string $other): bool
    {
        if (\is_string($other)) {
            return 0 === strcmp($this->reference, $other);
        }

        if (!$other instanceof self) {
            return false;
        }

        return 0 === strcmp($this->reference, $other->reference);
    }

    public function isNil(): bool
    {
        return uuid_is_null($this->reference);
    }

    public function __toString(): string
    {
        return $this->reference;
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->reference;
    }
}
