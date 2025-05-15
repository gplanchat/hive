<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Collection;

/**
 * @template Type
 */
final class Collection
{
    /**
     * @param \Iterator<mixed, Type> $items
     */
    private function __construct(
        private readonly \Iterator $items,
    ) {
    }

    /**
     * @param \Iterator<mixed, Type> $data
     *
     * @return self<Type>
     */
    public static function fromIterator(\Iterator $data): self
    {
        return new self($data);
    }

    /**
     * @param \Traversable<mixed, Type> $data
     *
     * @return self<Type>
     */
    public static function fromTraversable(\Traversable $data): self
    {
        return new self(new \IteratorIterator($data));
    }

    /**
     * @param array<mixed, Type> $data
     *
     * @return self<Type>
     */
    public static function fromArray(array $data): self
    {
        return new self(new \ArrayIterator($data));
    }

    /**
     * @return array<mixed, Type>
     */
    public function toArray(): array
    {
        return iterator_to_array($this->items, false);
    }

    /**
     * @param callable(Type): bool $filter
     *
     * @return Collection<Type>
     */
    public function filter(callable $filter): self
    {
        return new self(
            new \CallbackFilterIterator($this->items, $filter),
        );
    }

    /**
     * @template ReturnType
     *
     * @param callable(Type): ReturnType $map
     *
     * @return Collection<ReturnType>
     */
    public function map(callable $map): self
    {
        return new self(
            new MapIterator($this->items, $map(...))
        );
    }

    /**
     * @param callable(Type $left, Type $right): bool $callable
     *
     * @return Collection<Type>
     */
    public function unique(callable $callable): self
    {
        $values = $this->toArray();
        $left = self::fromArray($values);
        $right = self::fromArray($values);

        $index = 0;

        return $left->filter(function ($current) use ($callable, $right, &$index) {
            try {
                $slice = $right->offset(++$index);

                return $slice->none(fn ($cloned) => $callable($current, $cloned));
            } catch (\OutOfBoundsException) {
                return true;
            }
        });
    }

    /**
     * @return Collection<Type>
     */
    public function offset(int $offset): self
    {
        return new self(
            new \LimitIterator($this->items, $offset),
        );
    }

    /**
     * @return Collection<Type>
     */
    public function limit(int $limit): self
    {
        return new self(
            new \LimitIterator($this->items, 0, $limit),
        );
    }

    /**
     * @param callable(Type): bool $callable
     */
    public function any(callable $callable): bool
    {
        return array_any($this->toArray(), $callable);
    }

    /**
     * @param callable(Type): bool $callable
     */
    public function none(callable $callable): bool
    {
        return array_all($this->toArray(), fn ($item) => !$callable($item));
    }

    /**
     * @param callable(Type): bool $callable
     */
    public function all(callable $callable): bool
    {
        return array_all($this->toArray(), $callable);
    }
}
