<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query;

use App\Platform\Domain\Vault\Cipher\CipherInterface;
use App\Platform\Domain\Vault\ValueObject\CipheredInterface;
use App\Platform\Domain\Vault\ValueObject\CipheredText;

/**
 * @implements \IteratorAggregate<string, CipheredInterface>
 */
final readonly class OVHCloudCipheredCloudProviderCredentials implements CipheredCloudProviderCredentialsInterface, \IteratorAggregate
{
    public function __construct(
        public CipheredInterface $service,
        public CipheredInterface $applicationKey,
        public CipheredInterface $applicationSecret,
        public CipheredInterface $consumerKey,
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'service' => $this->service->asString(),
            'applicationKey' => $this->applicationKey->asString(),
            'applicationSecret' => $this->applicationSecret->asString(),
            'consumerKey' => $this->consumerKey->asString(),
        ];
    }

    /**
     * @param array{
     *     service: string,
     *     applicationKey: string,
     *     applicationSecret: string,
     *     consumerKey: string,
     * } $normalized
     * @return CipheredCloudProviderCredentialsInterface
     */
    public static function fromNormalized(array $normalized): CipheredCloudProviderCredentialsInterface
    {
        return new self(
            service: CipheredText::fromEncryptedString($normalized['service']),
            applicationKey: CipheredText::fromEncryptedString($normalized['applicationKey']),
            applicationSecret: CipheredText::fromEncryptedString($normalized['applicationSecret']),
            consumerKey: CipheredText::fromEncryptedString($normalized['consumerKey']),
        );
    }

    public function decrypt(CipherInterface $cipher): OVHCloudCloudProviderCredentials
    {
        return new OVHCloudCloudProviderCredentials(
            service: $cipher->decrypt($this->service),
            applicationKey: $cipher->decrypt($this->applicationKey),
            applicationSecret: $cipher->decrypt($this->applicationSecret),
            consumerKey: $cipher->decrypt($this->consumerKey),
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
