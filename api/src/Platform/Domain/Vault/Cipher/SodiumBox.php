<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\Cipher;

use App\Platform\Domain\Vault\InvalidEncryptedDataException;
use App\Platform\Domain\Vault\ValueObject\CipheredInterface;
use App\Platform\Domain\Vault\ValueObject\CipheredText;
use App\Platform\Domain\Vault\ValueObject\OpaqueInterface;
use App\Platform\Domain\Vault\ValueObject\OpaqueResource;
use App\Platform\Domain\Vault\ValueObject\OpaqueText;

use function Sodium\crypto_box;
use function Sodium\crypto_box_keypair_from_secretkey_and_publickey;
use function Sodium\crypto_box_open;

final class SodiumBox implements CipherInterface
{
    public function __construct(
        private readonly Keypair $senderKey,
        private readonly Keypair $receiverKey,
        private readonly NonceGeneratorInterface $nonceGenerator,
        private readonly int $threshold = 131072, // 128 kB
    ) {
    }

    public function encrypt(OpaqueInterface|string $clear): CipheredInterface
    {
        if (\is_string($clear)) {
            $clear = new OpaqueText($clear);
        }

        $nonce = $this->nonceGenerator->generate();
        $key = crypto_box_keypair_from_secretkey_and_publickey(
            $this->senderKey->secret->asString(),
            $this->receiverKey->public->asString(),
        );

        try {
            return new CipheredText($nonce, crypto_box($clear->asString(), $nonce, $key));
        } catch (\SodiumException $exception) {
            throw new InvalidEncryptedDataException('The provided encrypted data could not be decrypted.', previous: $exception);
        }
    }

    public function decrypt(CipheredInterface $ciphered): OpaqueInterface
    {
        $key = crypto_box_keypair_from_secretkey_and_publickey(
            $this->receiverKey->secret->asString(),
            $this->senderKey->public->asString(),
        );

        try {
            $clear = crypto_box_open($ciphered->encrypted(), $ciphered->nonce(), $key);
        } catch (\SodiumException $exception) {
            throw new InvalidEncryptedDataException('The provided encrypted data could not be decrypted.', previous: $exception);
        }

        if (false === $clear) {
            throw new InvalidEncryptedDataException('The provided encrypted data could not be decrypted.');
        }

        if (\strlen($clear) > $this->threshold) {
            return OpaqueResource::fromString($clear);
        }

        return new OpaqueText($clear);
    }
}
