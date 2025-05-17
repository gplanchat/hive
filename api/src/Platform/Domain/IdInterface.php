<?php

declare(strict_types=1);

namespace App\Platform\Domain;

interface IdInterface extends \Stringable
{
    /** @param non-empty-string $uri */
    public static function fromUri(string $uri): self;

    public function toString(): string;

    /** @param self|non-empty-string $other */
    public function equals(self|string $other): bool;
}
