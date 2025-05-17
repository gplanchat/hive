<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure;

interface EventBusInterface
{
    public function emit(object $event): void;
}
