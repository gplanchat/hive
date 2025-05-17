<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query;

use App\Platform\Domain\Vault\Cipher\CipherInterface;
use App\Platform\Domain\Vault\ValueObject\CipheredInterface;

/**
 * @extends \ArrayAccess<string, CipheredInterface>
 * @extends \Traversable<string, CipheredInterface>
 */
interface CipheredCloudProviderCredentialsInterface extends \JsonSerializable, \ArrayAccess, \Countable, \Traversable
{
    /**
     * @param array{} $normalized
     */
    public static function fromNormalized(array $normalized): self;

    public function decrypt(CipherInterface $cipher): CloudProviderCredentialsInterface;
}
