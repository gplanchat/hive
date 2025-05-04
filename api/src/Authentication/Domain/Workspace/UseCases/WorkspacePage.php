<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\UseCases;

use App\Authentication\Domain\Workspace\Workspace;

final readonly class WorkspacePage implements \IteratorAggregate, \Countable
{
    private array $workspaces;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        Workspace ...$workspaces
    ) {
        $this->workspaces = $workspaces;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->workspaces;
    }

    public function count(): int
    {
        return count($this->workspaces);
    }
}
