<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query;

use App\Platform\Domain\Vault\Cipher\CipherInterface;
use App\Platform\Domain\Vault\ValueObject\OpaqueInterface;

/**
 * @implements \IteratorAggregate<string, OpaqueInterface>
 */
final readonly class OVHCloudCloudProviderCredentials implements CloudProviderCredentialsInterface, \IteratorAggregate
{
    public function __construct(
        public OpaqueInterface $service,
        public OpaqueInterface $applicationKey,
        public OpaqueInterface $applicationSecret,
        public OpaqueInterface $consumerKey,
    ) {
    }

    public function encrypt(CipherInterface $cipher): OVHCloudCipheredCloudProviderCredentials
    {
        return new OVHCloudCipheredCloudProviderCredentials(
            service: $cipher->encrypt($this->service),
            applicationKey: $cipher->encrypt($this->applicationKey),
            applicationSecret: $cipher->encrypt($this->applicationSecret),
            consumerKey: $cipher->encrypt($this->consumerKey),
        );
    }

    public function offsetExists(mixed $offset): bool
    {
        return \in_array($offset, ['service', 'applicationKey', 'applicationSecret', 'consumerKey']);
    }

    public function offsetGet(mixed $offset): CipherInterface
    {
        if (!\in_array($offset, ['service', 'applicationKey', 'applicationSecret', 'consumerKey'])) {
            throw new \OutOfRangeException();
        }

        return $this->{$offset}->asString();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException();
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException();
    }

    public function count(): int
    {
        return 4;
    }

    public function getIterator(): \Traversable
    {
        yield 'service' => $this->service;
        yield 'applicationKey' => $this->applicationKey;
        yield 'applicationSecret' => $this->applicationSecret;
        yield 'consumerKey' => $this->consumerKey;
    }
}
