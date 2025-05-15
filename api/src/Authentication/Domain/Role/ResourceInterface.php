<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role;

use Symfony\Component\Routing\Requirement\Requirement;

interface ResourceInterface extends \BackedEnum
{
    public const REQUIREMENT = Requirement::ASCII_SLUG.'\.'.Requirement::ASCII_SLUG;
}
