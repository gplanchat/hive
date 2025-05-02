<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

interface IdInterface extends \Stringable
{
    public const NIL = '00000000-0000-0000-0000-000000000000';

    public static function fromUri(string $uri): self;

    public static function fromString(string $reference): self;

    public function toString(): string;

    public function equals(self|string $other): bool;

    public function isNil(): bool;
}
