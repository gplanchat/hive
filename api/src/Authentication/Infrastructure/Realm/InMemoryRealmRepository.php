<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\Realm;

use App\Authentication\Domain\Realm\Query\Realm;
use App\Authentication\Domain\Realm\RealmId;
use App\Authentication\Domain\Realm\Query\RealmRepositoryInterface;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\NotFoundException;

final class InMemoryRealmRepository implements RealmRepositoryInterface
{
    private array $storage = [];

    public function __construct(
        Realm ...$featureRollouts,
    ) {
        $this->storage = $featureRollouts;
    }

    public function get(RealmId $featureRolloutId): Realm
    {
        $result = array_filter($this->storage, fn (Realm $featureRollout) => $featureRollout->code->equals($featureRolloutId));

        return array_shift($result) ?? throw new NotFoundException();
    }

    public function list(int $currentPage = 1, int $pageSize = 25): RealmPage
    {
        $result = $this->storage;

        return new RealmPage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public static function buildTestRepository(): self
    {
        return new self(
            new Realm(
                RealmId::fromString('gyroscops'),
            ),
        );
    }
}
