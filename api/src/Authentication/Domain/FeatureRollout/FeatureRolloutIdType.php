<?php

declare(strict_types=1);

namespace App\Authentication\Domain\FeatureRollout;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class FeatureRolloutIdType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL([...$column, ...[
            'length' => 150,
            'nullable' => false,
        ]]);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (\is_string($value)) {
            return $value;
        }

        \assert($value instanceof FeatureRolloutId);

        return $value->toString();
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return FeatureRolloutId::fromString($value);
    }
}
