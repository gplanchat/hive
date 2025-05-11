<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Keycloak;

final readonly class OpenIDKey
{
    public function __construct(
        #[\SensitiveParameter]
        public string $kid,
        #[\SensitiveParameter]
        public string $kty,
        #[\SensitiveParameter]
        public string $alg,
        #[\SensitiveParameter]
        public string $use,
        #[\SensitiveParameter]
        public array $x5c,
        #[\SensitiveParameter]
        public string $x5t,
        #[\SensitiveParameter]
        public string $x5t_S256,
        #[\SensitiveParameter]
        public string $n,
        #[\SensitiveParameter]
        public string $e,
    ) {}
}
