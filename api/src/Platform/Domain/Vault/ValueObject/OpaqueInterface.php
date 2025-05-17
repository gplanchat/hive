<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\ValueObject;

interface OpaqueInterface extends BlobInterface
{
    /** @return array{} */
    public function __debugInfo(): ?array;

    /** @return array{} */
    public function __serialize(): array;

    /** @param array{} $data */
    public function __unserialize(array $data): void;
}
