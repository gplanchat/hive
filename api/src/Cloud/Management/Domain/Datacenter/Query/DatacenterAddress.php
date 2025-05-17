<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\Datacenter\Query;

use App\Platform\Domain\Countries;

final readonly class DatacenterAddress
{
    public function __construct(
        public string $company,
        public string $street,
        public string $number,
        public string $postalCode,
        public string $city,
        public Countries $country,
    ) {
    }
}
