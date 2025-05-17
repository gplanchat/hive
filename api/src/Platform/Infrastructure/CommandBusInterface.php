<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure;

interface CommandBusInterface
{
    public function apply(object $command): void;
}
