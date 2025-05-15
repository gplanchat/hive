<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Realm\Query\UseCases;

use App\Authentication\Domain\Realm\Query\Realm;

/**
 * @implements \IteratorAggregate<mixed, Realm>
 */
final readonly class RealmPage implements \IteratorAggregate, \Countable
{
    /** @var Realm[] */
    private array $realms;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        Realm ...$realms,
    ) {
        $this->realms = array_values($realms);
    }

    /**
     * @implements \IteratorAggregate<mixed, Realm>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->realms;
    }

    public function count(): int
    {
        return \count($this->realms);
    }
}
