<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role\Query\UseCases;

use App\Authentication\Domain\Role\Query\Role;

/**
 * @implements \IteratorAggregate<mixed, Role>
 */
final readonly class RolePage implements \IteratorAggregate, \Countable
{
    /**
     * @var Role[]
     */
    private array $roles;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        Role ...$roles,
    ) {
        $this->roles = $roles;
    }

    /**
     * @return \Traversable<mixed, Role>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->roles;
    }

    public function count(): int
    {
        return \count($this->roles);
    }
}
