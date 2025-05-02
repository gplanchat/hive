<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Organization\Query\UseCases;

use App\Authentication\Domain\Organization\Query\Organization;

final readonly class OrganizationPage implements \IteratorAggregate, \Countable
{
    private array $organizations;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        Organization ...$organizations
    ) {
        $this->organizations = $organizations;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->organizations;
    }

    public function count(): int
    {
        return count($this->organizations);
    }
}
