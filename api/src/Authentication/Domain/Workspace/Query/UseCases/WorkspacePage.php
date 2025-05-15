<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Workspace\Query\UseCases;

use App\Authentication\Domain\Workspace\Query\Workspace;

/**
 * @implements \IteratorAggregate<mixed, Workspace>
 */
final readonly class WorkspacePage implements \IteratorAggregate, \Countable
{
    /**
     * @var Workspace[]
     */
    private array $workspaces;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        Workspace ...$workspaces,
    ) {
        $this->workspaces = array_values($workspaces);
    }

    /**
     * @return \Traversable<mixed, Workspace>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->workspaces;
    }

    public function count(): int
    {
        return \count($this->workspaces);
    }
}
