<?php

declare(strict_types=1);

namespace App\Authentication\UserInterface\Workspace;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Context;

final readonly class DisableWorkspaceInput
{
    public function __construct(
        #[ApiProperty(
            description: 'End date of validity of the subscription',
            schema: ['type' => 'string', 'format' => 'date'],
        )]
        #[Context(['datetime_format' => 'Y-m-d'])]
        public ?\DateTimeInterface $validUntil = null,
    ) {
    }
}
