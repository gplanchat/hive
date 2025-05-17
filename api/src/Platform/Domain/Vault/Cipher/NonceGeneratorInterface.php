<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\Cipher;

interface NonceGeneratorInterface
{
    public function generate(): string;
}
