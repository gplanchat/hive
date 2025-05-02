<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Command;

use App\Authentication\Domain\User\UserId;

final readonly class DeletedEvent
{
    public function __construct(
        public UserId $uuid,
        public int $version,
    ) {
    }
}
