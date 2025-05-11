<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Collection;

final readonly class MapIterator implements \Iterator
{
    public function __construct(
        private \Iterator $decorated,
        private \Closure $map,
    ) {
    }

    public function current(): mixed
    {
        return ($this->map)($this->decorated->current());
    }

    public function next(): void
    {
        $this->decorated->next();
    }

    public function key(): mixed
    {
        return $this->decorated->key();
    }

    public function valid(): bool
    {
        return $this->decorated->valid();
    }

    public function rewind(): void
    {
        $this->decorated->rewind();
    }
}
