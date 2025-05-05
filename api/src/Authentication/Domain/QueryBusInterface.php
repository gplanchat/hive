<?php

namespace App\Authentication\Domain;

interface QueryBusInterface
{
    public function query(object $query): object;
}
