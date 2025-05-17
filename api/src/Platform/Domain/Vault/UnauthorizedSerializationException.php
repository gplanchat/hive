<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault;

final class UnauthorizedSerializationException extends \RuntimeException
{
    /** @param class-string $className */
    public static function forClass(string $className): self
    {
        return new self('The '.$className.' class cannot be unserialized as it is an Opaque or Secret object.');
    }
}
