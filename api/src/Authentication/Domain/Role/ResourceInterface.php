<?php

namespace App\Authentication\Domain\Role;

use Symfony\Component\Routing\Requirement\Requirement;

interface ResourceInterface extends \BackedEnum
{
    const REQUIREMENT = Requirement::ASCII_SLUG.'\.'.Requirement::ASCII_SLUG;
}
