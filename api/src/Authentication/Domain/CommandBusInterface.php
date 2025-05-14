<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

interface CommandBusInterface
{
    public function apply(object $command): void;
}
