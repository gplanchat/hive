<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure;

use App\Platform\Infrastructure\Collection\Collection;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Adapter\TraceableTagAwareAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class StorageMock implements AdapterInterface, TagAwareCacheInterface
{
    private ArrayAdapter $adapter;
    private AdapterInterface&TagAwareCacheInterface $decorated;

    public function __construct()
    {
        $this->adapter = new ArrayAdapter(storeSerialized: false);
        $this->decorated = new TraceableTagAwareAdapter(
            new TagAwareAdapter($this->adapter),
        );
    }

    public function getItem(mixed $key): CacheItem
    {
        return $this->decorated->getItem($key);
    }

    public function getItems(array $keys = []): iterable
    {
        return $this->decorated->getItems($keys);
    }

    public function clear(string $prefix = ''): bool
    {
        return $this->decorated->clear($prefix);
    }

    public function hasItem(string $key): bool
    {
        return $this->decorated->hasItem($key);
    }

    public function deleteItem(string $key): bool
    {
        return $this->decorated->deleteItem($key);
    }

    public function deleteItems(array $keys): bool
    {
        return $this->decorated->deleteItems($keys);
    }

    public function save(CacheItemInterface $item): bool
    {
        return $this->decorated->save($item);
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->decorated->saveDeferred($item);
    }

    public function commit(): bool
    {
        return $this->decorated->commit();
    }

    public function invalidateTags(array $tags): bool
    {
        return $this->decorated->invalidateTags($tags);
    }

    /**
     * @param array{expiry: int, ctime: int, tags: string[]}|null $metadata
     */
    public function get(string $key, callable $callback, ?float $beta = null, ?array &$metadata = null): mixed
    {
        return $this->decorated->get($key, $callback, $beta, $metadata);
    }

    public function delete(string $key): bool
    {
        return $this->decorated->delete($key);
    }

    /**
     * @return Collection<mixed>
     */
    public function getValues(): Collection
    {
        return Collection::fromArray($this->adapter->getValues());
    }
}
