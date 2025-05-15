<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Realm;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\Query\RealmRepositoryInterface;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\Realm\RealmId;
use App\Shared\Infrastructure\Collection\Collection;

final class InMemoryRealmRepository implements RealmRepositoryInterface
{
    /**
     * @var Realm[]
     */
    private array $storage = [];

    public function __construct(
        Realm ...$realms,
    ) {
        $this->storage = $realms;
    }

    public function get(RealmId $realmId): Realm
    {
        $result = Collection::fromArray($this->storage)
            ->filter(fn (Realm $realm) => $realm->code->equals($realmId))
            ->toArray()
        ;

        return array_shift($result) ?? throw new NotFoundException();
    }

    public function list(int $currentPage = 1, int $pageSize = 25): RealmPage
    {
        $result = $this->storage;

        return new RealmPage(
            $currentPage,
            $pageSize,
            \count($result),
            ...\array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public static function buildTestRepository(): self
    {
        return new self(
            new Realm(
                RealmId::fromString('acme-inc'),
                'Acme Inc.',
            ),
        );
    }
}
