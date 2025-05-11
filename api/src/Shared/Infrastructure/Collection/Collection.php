<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Collection;

final class Collection
{
    private function __construct(
        private readonly \Iterator $items,
    ) {}

    public static function fromIterator(\Iterator $data): self
    {
        return new self($data);
    }

    public static function fromTraversable(\Traversable $data): self
    {
        return new self(new \IteratorIterator($data));
    }

    public static function fromArray(array $data): self
    {
        return new self(new \ArrayIterator($data));
    }

    public function toArray(): array
    {
        return iterator_to_array($this->items, false);
    }

    public function filter(callable $filter): self
    {
        return new self(
            new \CallbackFilterIterator($this->items, $filter),
        );
    }

    public function map(callable $map): self
    {
        return new self(
            new MapIterator($this->items, $map(...))
        );
    }

    public function unique(callable $callable): self
    {
        $values = $this->toArray();
        $left = self::fromArray($values);
        $right = self::fromArray($values);

        $index = 0;
        return $left->filter(function ($current) use ($callable, $right, &$index) {
            try {
                $slice = $right->offset(++$index);

                return $slice->none(function ($cloned) use ($callable, $current) {
                    return $callable($current, $cloned);
                });
            } catch (\OutOfBoundsException) {
                return true;
            }
        });
    }

    public function offset(int $offset): self
    {
        return new self(
            new \LimitIterator($this->items, $offset),
        );
    }

    public function limit(int $limit): self
    {
        return new self(
            new \LimitIterator($this->items, 0, $limit),
        );
    }

    public function any(callable $callable): bool
    {
        return array_any($this->toArray(), $callable);
    }

    public function none(callable $callable): bool
    {
        return array_all($this->toArray(), fn ($item) => !$callable($item));
    }

    public function all(callable $callable): bool
    {
        return array_all($this->toArray(), $callable);
    }
}
