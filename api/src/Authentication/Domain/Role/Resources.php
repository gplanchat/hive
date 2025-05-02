<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role;

enum Resources: string implements ResourceInterface
{
    case Organization = 'authentication.organization';
    case User = 'authentication.user';
    case Role = 'authentication.role';
    case Workspace = 'authentication.workspace';
}
