<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

use App\Shared\Infrastructure\Collection\Collection;
use Firebase\JWT\Key;

/**
 * @internal This class should only be used for testing purposes
 */
final readonly class KeysRegistry
{
    /**
     * @var list<array{0: Key, 1: Key}>
     */
    private array $keys;

    /** @param array{0: Key, 1: Key} ...$keys */
    private function __construct(
        array ...$keys,
    ) {
        $this->keys = array_values($keys);
    }

    public static function create(): self
    {
        return new self([
            new Key('secret', 'HS256'),
            new Key('secret', 'HS256'),
        ]);
    }

    /** @return Collection<Key> */
    public function publicKeys(): Collection
    {
        return Collection::fromArray(array_column($this->keys, 1));
    }

    /** @return Collection<Key> */
    public function privateKeys(): Collection
    {
        return Collection::fromArray(array_column($this->keys, 0));
    }
}
