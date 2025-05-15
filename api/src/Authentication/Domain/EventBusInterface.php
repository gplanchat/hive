<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

interface EventBusInterface
{
    public function emit(object $event): void;
}
