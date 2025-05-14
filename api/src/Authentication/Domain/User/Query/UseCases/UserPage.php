<?php

declare(strict_types=1);

namespace App\Authentication\Domain\User\Query\UseCases;

use App\Authentication\Domain\User\Query\User;

final readonly class UserPage implements \IteratorAggregate, \Countable
{
    private array $users;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        User ...$users,
    ) {
        $this->users = $users;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->users;
    }

    public function count(): int
    {
        return \count($this->users);
    }
}
