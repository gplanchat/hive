<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\ValueObject;

interface BlobInterface extends \Stringable
{
    public function length(): int;

    public function asString(): string;

    /** @return resource */
    public function asResource();
}
