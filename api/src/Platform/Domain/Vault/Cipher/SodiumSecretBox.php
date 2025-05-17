<?php

declare(strict_types=1);

namespace App\Platform\Domain\Vault\Cipher;

use App\Platform\Domain\Vault\InvalidEncryptedDataException;
use App\Platform\Domain\Vault\ValueObject\CipheredInterface;
use App\Platform\Domain\Vault\ValueObject\CipheredText;
use App\Platform\Domain\Vault\ValueObject\OpaqueInterface;
use App\Platform\Domain\Vault\ValueObject\OpaqueKey;
use App\Platform\Domain\Vault\ValueObject\OpaqueResource;
use App\Platform\Domain\Vault\ValueObject\OpaqueText;

use function Sodium\crypto_secretbox;
use function Sodium\crypto_secretbox_open;

final class SodiumSecretBox implements CipherInterface
{
    public function __construct(
        private readonly OpaqueKey $key,
        private readonly NonceGeneratorInterface $nonce,
        private readonly int $threshold = 131072, // 128 kB
    ) {
    }

    public function encrypt(OpaqueInterface|string $clear): CipheredInterface
    {
        if (\is_string($clear)) {
            $clear = new OpaqueText($clear);
        }

        $nonce = $this->nonce->generate();

        try {
            return new CipheredText($nonce, crypto_secretbox($clear->asString(), $nonce, $this->key->asString()));
        } catch (\SodiumException $exception) {
            throw new InvalidEncryptedDataException('The provided encrypted data could not be decrypted.', previous: $exception);
        }
    }

    public function decrypt(CipheredInterface $ciphered): OpaqueInterface
    {
        try {
            $clear = crypto_secretbox_open($ciphered->encrypted(), $ciphered->nonce(), $this->key->asString());
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
