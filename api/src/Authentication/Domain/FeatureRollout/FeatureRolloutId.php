<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout;

use App\Authentication\Domain\IdInterface;
use Symfony\Component\Routing\Requirement\Requirement;

final class FeatureRolloutId implements IdInterface
{
    const string REQUIREMENT = '\/authentication\/feature-rollout\/'.Requirement::ASCII_SLUG;
    const string PARSE = '\/authentication\/feature-rollout\/?<reference>'.Requirement::ASCII_SLUG.')';

    private function __construct(
        private readonly string $reference,
    ) {
        if (!preg_match('/'.Requirement::ASCII_SLUG.'/', $this->reference)) {
            throw new \InvalidArgumentException(\sprintf('<%s> is not a valid Feature Rollout code.', $reference));
        }
    }

    public static function fromUri(string $uri): IdInterface
    {
        if (!preg_match(self::PARSE, $uri, $matches)) {
            throw new \InvalidArgumentException(\sprintf('<%s> is not a valid Feature Rollout code.', $reference));
        }

        return new self($matches['reference']);
    }

    public static function fromString(string $reference): self
    {
        return new self($reference);
    }

    public function equals(IdInterface|string $other): bool
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
        return 0 === strcmp($this->reference, '');
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
