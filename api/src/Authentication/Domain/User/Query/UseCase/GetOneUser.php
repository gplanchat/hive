<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCase;

use App\Authentication\Domain\User\UserId;

final readonly class GetOneUser
{
    public function __construct(
        public UserId $uuid,
    ) {
    }
}
