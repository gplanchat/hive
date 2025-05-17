<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure;

interface QueryBusInterface
{
    public function query(object $query): object;
}
