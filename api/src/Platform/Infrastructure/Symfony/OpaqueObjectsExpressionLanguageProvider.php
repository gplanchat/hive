<?php

declare(strict_types=1);

namespace App\Platform\Infrastructure\Symfony;

use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class OpaqueObjectsExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new OpaqueTextFromEnvFunction(),
            new OpaqueKeyFromEnvFunction(),
            new OpaqueBase64KeyFromEnvFunction(),
        ];
    }
}
