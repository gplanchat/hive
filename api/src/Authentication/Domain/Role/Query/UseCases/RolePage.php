<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

use App\Authentication\Domain\Role\Query\Role;

final readonly class RolePage implements \IteratorAggregate, \Countable
{
    private array $roles;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        Role ...$roles,
    ) {
        $this->roles = $roles;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->roles;
    }

    public function count(): int
    {
        return \count($this->roles);
    }
}
