<?php

namespace App\Authentication\Domain;

interface EventBusInterface
{
    public function emit(object $event): void;
}
