<?php

declare(strict_types=1);

namespace App\Authentication\Domain\Role;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Routing\Requirement\Requirement;

final readonly class ResourceAccess
{
    /** @var ActionInterface[] */
    #[ApiProperty(
        description: 'Resource allowed actions',
        schema: ['type' => 'list', 'items' => ['type' => 'string', 'pattern' => Requirement::ASCII_SLUG, 'minLength' => 3, 'maxLength' => 100]],
    )]
    public array $actions;

    public function __construct(
        #[ApiProperty(
            description: 'Resource identifier',
            schema: ['type' => 'string', 'pattern' => Requirement::ASCII_SLUG.'\.'.Requirement::ASCII_SLUG, 'minLength' => 3, 'maxLength' => 100],
        )]
        public ResourceInterface $resource,
        ActionInterface ...$actions
    ) {
        $this->actions = $actions;
    }
}
