<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role;

use Symfony\Component\Routing\Requirement\Requirement;

interface ActionInterface extends \BackedEnum
{
    public const REQUIREMENT = Requirement::ASCII_SLUG;
}
