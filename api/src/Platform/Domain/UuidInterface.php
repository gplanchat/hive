<?php

declare(strict_types=1);

namespace App\Platform\Domain;

interface UuidInterface extends IdInterface
{
    public const NIL = '00000000-0000-0000-0000-000000000000';

    /** @param non-empty-string $reference */
    public static function fromString(string $reference): self;

    public static function generateRandom(): self;

    public static function nil(): self;

    public function isNil(): bool;
}
