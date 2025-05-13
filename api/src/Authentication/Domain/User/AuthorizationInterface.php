<?php

namespace App\Authentication\Domain\User;

interface AuthorizationInterface extends \JsonSerializable
{
    public static function fromNormalized(array $normalized): self;
}
