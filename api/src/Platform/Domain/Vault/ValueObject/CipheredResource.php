<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\ValueObject;

use App\Platform\Domain\Vault\ResourceInitializationException;
use App\Platform\Domain\Vault\UnauthorizedSerializationException;

use const Sodium\CRYPTO_BOX_NONCEBYTES;

final class CipheredResource implements CipheredInterface
{
    public function __construct(
        #[\SensitiveParameter]
        private readonly string $nonce,
        #[\SensitiveParameter]
        private readonly mixed $encrypted,
    ) {
        if (!\is_resource($this->encrypted)) {
            throw new \InvalidArgumentException('Second argument should be a resource');
        }
    }

    public static function fromEncryptedString(#[\SensitiveParameter] string $encrypted): self
    {
        $nonce = mb_substr($encrypted, 0, CRYPTO_BOX_NONCEBYTES, '8bit');
        $resource = fopen('php://temp', 'r+');
        if (false === $resource) {
            $error = error_get_last();
            throw new ResourceInitializationException(message: 'We could not create a new temporary stream to store the ciphered text into a resource. This me be caused by a memory or storage limit.', previous: new \ErrorException(message: $error['message'], severity: $error['type'], filename: $error['file'], line: $error['line']));
        }
        fwrite($resource, mb_substr($encrypted, CRYPTO_BOX_NONCEBYTES, null, '8bit'));
        fseek($resource, 0, \SEEK_SET);

        return new self($nonce, $resource);
    }

    public static function fromBase64EncryptedString(#[\SensitiveParameter] string $encoded): self
    {
        error_clear_last();
        $encrypted = base64_decode($encoded, true);
        if (false === $encrypted) {
            $error = error_get_last();
            throw new ResourceInitializationException(message: 'We could not decode the binary source while decoding the base64 input.', previous: new \ErrorException(message: $error['message'], severity: $error['type'], filename: $error['file'], line: $error['line']));
        }

        return self::fromEncryptedString($encrypted);
    }

    /** @param resource $encrypted */
    public static function fromEncryptedResource(#[\SensitiveParameter] $encrypted): self
    {
        return new self(
            fread($encrypted, CRYPTO_BOX_NONCEBYTES),
            $encrypted
        );
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function encrypted(): string
    {
        return stream_get_contents($this->encrypted);
    }

    public function asString(): string
    {
        return $this->nonce.$this->encrypted();
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
        return 'ciphered:**SECRET**';
    }

    /**
     * @return array{nonce: '**SECRET**', encrypted: '**SECRET**'}
     */
    public function __debugInfo(): array
    {
        return [
            'nonce' => '**SECRET**',
            'encrypted' => '**SECRET**',
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
