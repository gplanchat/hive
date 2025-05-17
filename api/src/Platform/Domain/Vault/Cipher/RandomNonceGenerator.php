<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\Cipher;

use App\Platform\Domain\Vault\NonceCreationFailureException;
use Random\RandomException;

use const Sodium\CRYPTO_BOX_NONCEBYTES;

final class RandomNonceGenerator implements NonceGeneratorInterface
{
    public function generate(): string
    {
        try {
            return random_bytes(CRYPTO_BOX_NONCEBYTES);
        } catch (RandomException $exception) {
            throw new NonceCreationFailureException(previous: $exception);
        }
    }
}
