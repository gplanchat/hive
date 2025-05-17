<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure\Symfony;

use App\Platform\Domain\Vault\ValueObject\OpaqueText;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;

final class OpaqueTextFromEnvFunction extends ExpressionFunction
{
    public function __construct(string $name = 'opaqueFromEnv')
    {
        parent::__construct(
            $name,
            fn (string $argument) => <<<PHP
                is_string(\$env = \$container->getEnv({$argument}))
                    ? new \\App\\Platform\\Domain\\Vault\\ValueObject\\OpaqueText(\$env)
                    : throw new \\RuntimeException('The {$argument} environment variable is not defined.')
                PHP,
            fn (array $variables, #[\SensitiveParameter] string $argument) => \is_string($env = $variables['container']->getEnv($argument))
                    ? new OpaqueText($env)
                    : throw new \RuntimeException("The {$argument} environment variable is not defined."),
        );
    }
}
