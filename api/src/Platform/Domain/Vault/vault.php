<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault;

use App\Platform\Domain\Vault\ValueObject\OpaqueInterface;

function disclose(OpaqueInterface $opaque): string
{
    return $opaque->asString();
}

function opaqueFromFile(string $filename): OpaqueInterface
{
}

function opaqueFromFile(string $filename): OpaqueInterface
{
}
