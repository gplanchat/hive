<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

use App\Authentication\Domain\User\AuthorizationInterface;

interface SecurityContextInterface
{
    public function authorization(): AuthorizationInterface;
}
