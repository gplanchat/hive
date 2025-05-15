<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User;

interface AuthorizationInterface extends \JsonSerializable
{
    /**
     * @param array{} $normalized
     */
    public static function fromNormalized(array $normalized): self;
}
