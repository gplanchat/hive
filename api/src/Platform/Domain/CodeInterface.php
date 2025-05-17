<?php

declare(strict_types=1);

namespace App\Platform\Domain;

interface CodeInterface extends IdInterface
{
    /** @param non-empty-string $reference */
    public static function fromString(string $reference): self;
}
