<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\Cipher;

use App\Platform\Domain\Vault\ValueObject\CipheredInterface;
use App\Platform\Domain\Vault\ValueObject\OpaqueInterface;

interface CipherInterface
{
    public function encrypt(OpaqueInterface|string $clear): CipheredInterface;

    public function decrypt(CipheredInterface $ciphered): OpaqueInterface;
}
