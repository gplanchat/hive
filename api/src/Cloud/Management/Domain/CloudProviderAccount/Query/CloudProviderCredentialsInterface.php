<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query;

use App\Platform\Domain\Vault\Cipher\CipherInterface;
use App\Platform\Domain\Vault\ValueObject\OpaqueInterface;

/**
 * @extends \ArrayAccess<string, OpaqueInterface>
 * @extends \Traversable<string, OpaqueInterface>
 */
interface CloudProviderCredentialsInterface extends \ArrayAccess, \Countable, \Traversable
{
    public function encrypt(CipherInterface $cipher): CipheredCloudProviderCredentialsInterface;
}
