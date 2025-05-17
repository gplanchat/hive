<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\Datacenter\Query;

interface PlatformCapabilityInterface extends \JsonSerializable
{
    /**
     * @param array{} $normalized
     */
    public static function fromNormalized(array $normalized): self;
}
