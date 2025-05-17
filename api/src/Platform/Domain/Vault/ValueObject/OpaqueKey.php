<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\ValueObject;

use App\Platform\Domain\Vault\ResourceInitializationException;
use App\Platform\Domain\Vault\UnauthorizedSerializationException;

final readonly class OpaqueKey implements OpaqueInterface
{
    public function __construct(
        #[\SensitiveParameter] private string $key,
    ) {
    }

    public static function generate(): self
    {
        return new self(sodium_crypto_secretbox_keygen());
    }

    public static function fromBase64(#[\SensitiveParameter] string $base64Encoded): self
    {
        $decoded = base64_decode($base64Encoded, true);
        if (false === $decoded) {
            throw new ResourceInitializationException('The provided key could not be decoded.');
        }

        return new self($decoded);
    }

    public function length(): int
    {
        return \strlen($this->asString());
    }

    public function asString(): string
    {
        return $this->key;
    }

    /**
     * @return resource
     *
     * @throws ResourceInitializationException
     */
    public function asResource()
    {
        $resource = fopen('php://temp', 'r+');
        if (false === $resource) {
            $error = error_get_last();
            throw new ResourceInitializationException(message: 'We could not create a new temporary stream to store the ciphered text into a resource. This me be caused by a memory or storage limit.', previous: new \ErrorException(message: $error['message'], severity: $error['type'], filename: $error['file'], line: $error['line']));
        }
        fwrite($resource, $this->asString());
        fseek($resource, 0, \SEEK_SET);

        return $resource;
    }

    public function __toString(): string
    {
        return '**SECRET**';
    }

    /**
     * @return array{content: '**SECRET**'}|null
     */
    public function __debugInfo(): ?array
    {
        return [
            'key' => '**SECRET**',
        ];
    }

    /** @return array{} */
    public function __serialize(): array
    {
        return [];
    }

    /** @param array{} $data */
    public function __unserialize(#[\SensitiveParameter] array $data): void
    {
        throw UnauthorizedSerializationException::forClass($this::class);
    }
}
