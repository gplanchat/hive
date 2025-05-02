<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role;

enum Actions: string implements ActionInterface
{
    case Show = 'Show';
    case List = 'List';
    case Create = 'Create';
    case Update = 'Update';
    case Delete = 'Delete';
}
