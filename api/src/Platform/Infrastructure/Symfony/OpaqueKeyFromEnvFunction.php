<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure\Symfony;

use App\Platform\Domain\Vault\ValueObject\OpaqueKey;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final class OpaqueKeyFromEnvFunction extends ExpressionFunction
{
    public function __construct(string $name = 'opaqueKeyFromEnv')
    {
        parent::__construct(
            $name,
            fn (string $argument) => <<<PHP
                is_string(\$env = \$container->getEnv({$argument}))
                    ? new \\App\\Platform\\Domain\\Vault\\ValueObject\\OpaqueKey(\$env)
                    : throw new \\RuntimeException('The {$argument} environment variable is not defined.')
                PHP,
            fn (array $variables, #[\SensitiveParameter] string $argument) => \is_string($env = $variables['container']->getEnv($argument))
                    ? new OpaqueKey($env)
                    : throw new \RuntimeException("The {$argument} environment variable is not defined."),
        );
    }
}
