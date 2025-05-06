<?php

namespace App\Authentication\Domain\Realm\Query;

use App\Authentication\Domain\NotFoundException;
use App\Authentication\Domain\Realm\Query\UseCases\RealmPage;
use App\Authentication\Domain\Realm\RealmId;

interface RealmRepositoryInterface
{
    /** @throws NotFoundException */
    public function get(RealmId $featureRolloutId): Realm;
    public function list(int $currentPage = 1, int $pageSize = 25): RealmPage;
}
