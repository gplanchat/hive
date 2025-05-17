<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount;

enum CloudProviderTypes: string
{
    case OVHCloud = 'ovh-cloud';
}
