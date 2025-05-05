<?php

namespace App\Authentication\Domain;

interface CommandBusInterface
{
    public function apply(object $command): void;
}
