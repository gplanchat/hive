<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\Cipher;

use App\Platform\Domain\Vault\ValueObject\CipheredInterface;
use App\Platform\Domain\Vault\ValueObject\OpaqueInterface;
use App\Platform\Domain\Vault\ValueObject\OpaqueResource;
use App\Platform\Domain\Vault\ValueObject\OpaqueText;

final class Unsecure implements CipherInterface
{
    public function __construct(
        private readonly int $threshold = 131072, // 128 kB
    ) {
    }

    public function encrypt(OpaqueInterface|string $clear): CipheredInterface
    {
        if (!\is_string($clear)) {
            $clear = $clear->asString();
        }

        return new ClearText($clear);
    }

    public function decrypt(CipheredInterface $ciphered): OpaqueInterface
    {
        if (\strlen($ciphered->asString()) > $this->threshold) {
            return OpaqueResource::fromString($ciphered->asString());
        }

        return new OpaqueText($ciphered->asString());
    }
}
