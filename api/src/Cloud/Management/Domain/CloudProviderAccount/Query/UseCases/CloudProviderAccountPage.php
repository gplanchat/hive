<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases;

use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccount;

/**
 * @implements \IteratorAggregate<int, CloudProviderAccount>
 */
final readonly class CloudProviderAccountPage implements \IteratorAggregate, \Countable
{
    /** @var CloudProviderAccount[] */
    private array $cloudProviders;

    public function __construct(
        public int $page,
        public int $pageSize,
        public int $totalItems,
        CloudProviderAccount ...$cloudProviders,
    ) {
        $this->cloudProviders = $cloudProviders;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->cloudProviders;
    }

    public function count(): int
    {
        return \count($this->cloudProviders);
    }
}
