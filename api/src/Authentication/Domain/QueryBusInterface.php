<?php

declare(strict_types=1);

namespace App\Authentication\Domain;

interface QueryBusInterface
{
    public function query(object $query): object;
}
