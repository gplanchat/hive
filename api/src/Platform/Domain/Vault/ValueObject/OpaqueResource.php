<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\ValueObject;

use App\Platform\Domain\Vault\ResourceInitializationException;
use App\Platform\Domain\Vault\UnauthorizedSerializationException;

final class OpaqueResource implements OpaqueInterface
{
    /**
     * @param resource $resource
     *
     * @throws \TypeError
     */
    public function __construct(
        #[\SensitiveParameter]
        private $resource,
    ) {
        if (!\is_resource($this->resource)
            || 'stream' !== get_resource_type($this->resource)
        ) {
            throw new \TypeError(\sprintf('The provided value should be a resource. %s provided.', get_debug_type($this->resource)));
        }
    }

    /**
     * @throws ResourceInitializationException
     */
    public static function fromFile(#[\SensitiveParameter] string $path): self
    {
        error_clear_last();
        $resource = fopen($path, 'r+');
        if (false === $resource) {
            $error = error_get_last();
            throw new ResourceInitializationException(message: 'We could not open the file at the provided location. This may be caused by a memory limit, a storage limit or an failed authorization to access the file.', previous: new \ErrorException(message: $error['message'] ?? '', severity: $error['type'] ?? 1, filename: $error['file'] ?? '', line: $error['line'] ?? -1));
        }

        return new self($resource);
    }

    /**
     * @throws ResourceInitializationException
     */
    public static function fromReadOnlyFile(#[\SensitiveParameter] string $path): self
    {
        error_clear_last();
        $resource = fopen($path, 'r');
        if (false === $resource) {
            $error = error_get_last();
            throw new ResourceInitializationException(message: 'We could not open the file at the provided location. This may be caused by a memory limit, a storage limit or an failed authorization to access the file.', previous: new \ErrorException(message: $error['message'] ?? '', severity: $error['type'] ?? 1, filename: $error['file'] ?? '', line: $error['line'] ?? -1));
        }

        return new self($resource);
    }

    /**
     * @throws ResourceInitializationException
     */
    public static function fromString(#[\SensitiveParameter] string $content): self
    {
        error_clear_last();
        $resource = fopen('php://temp', 'r+');
        if (false === $resource) {
            $error = error_get_last();
            throw new ResourceInitializationException(message: 'We could not create a new temporary stream to store the opaque text into a resource. This me be caused by a memory or storage limit.', previous: new \ErrorException(message: $error['message'], severity: $error['type'], filename: $error['file'], line: $error['line']));
        }
        fwrite($resource, $content);
        fseek($resource, 0, \SEEK_SET);

        return new self($resource);
    }

    public static function fromBase64(#[\SensitiveParameter] string $base64Encoded): self
    {
        error_clear_last();
        $decoded = base64_decode($base64Encoded, true);
        if (false === $decoded) {
            $error = error_get_last();
            throw new ResourceInitializationException(message: 'We could not decode the binary source while decoding the base64 input.', previous: new \ErrorException(message: $error['message'], severity: $error['type'], filename: $error['file'], line: $error['line']));
        }

        return new self($decoded);
    }

    public function asString(): string
    {
        return stream_get_contents($this->resource);
    }

    /** @return resource */
    public function asResource()
    {
        return $this->resource;
    }

    public function __toString(): string
    {
        return 'resource:**OPAQUE**';
    }

    /**
     * @return array{resource: '**OPAQUE**'}
     */
    public function __debugInfo(): array
    {
        return [
            'resource' => '**OPAQUE**',
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
