<?php

namespace App\Authentication\Domain\Role;

use Symfony\Component\Routing\Requirement\Requirement;

interface ActionInterface extends \BackedEnum
{
    const REQUIREMENT = Requirement::ASCII_SLUG;
}
