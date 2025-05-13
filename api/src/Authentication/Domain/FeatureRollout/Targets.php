<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout;

enum Targets: string
{
    case Global = 'Global';
    case Organization = 'Organization';
}
