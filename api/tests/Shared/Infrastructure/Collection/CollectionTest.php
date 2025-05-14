<?php

declare(strict_types=1);

namespace App\Tests\Shared\Infrastructure\Collection;

use App\Authentication\Domain\Realm\RealmId;
use App\Shared\Infrastructure\Collection\Collection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CollectionTest extends TestCase
{
    /** @test */
    public function itChecksAnyValue(): void
    {
        $this->assertTrue(Collection::fromArray(['lorem', 'ipsum', 'dolor'])->any(fn (string $current) => 0 === strcmp($current, 'lorem')));
        $this->assertFalse(Collection::fromArray(['lorem', 'ipsum', 'dolor'])->any(fn (string $current) => 0 === strcmp($current, 'sit amet')));
    }

    /** @test */
    public function itChecksNoValue(): void
    {
        $this->assertTrue(Collection::fromArray(['lorem', 'ipsum', 'dolor'])->none(fn (string $current) => 0 === strcmp($current, 'sit amet')));
        $this->assertFalse(Collection::fromArray(['lorem', 'ipsum', 'dolor'])->none(fn (string $current) => 0 === strcmp($current, 'lorem')));
    }

    /** @test */
    public function itChecksAllValues(): void
    {
        $this->assertTrue(Collection::fromArray(['lorem', 'ipsum', 'dolor'])->all(fn (string $current) => \is_string($current)));
        $this->assertFalse(Collection::fromArray(['lorem', 'ipsum', 'dolor'])->all(fn (string $current) => \is_int($current)));
        $this->assertFalse(Collection::fromArray(['lorem', 'ipsum', 'dolor'])->all(fn (string $current) => 0 === strcmp($current, 'lorem')));
    }

    /** @test */
    public function itFilterValues(): void
    {
        $this->assertEquals(
            ['lorem', 'lorem', 'lorem'],
            Collection::fromArray(['lorem', 'ipsum', 'dolor', 'lorem', 'dolor', 'lorem', 'ipsum', 'dolor'])
                ->filter(fn (string $current) => 0 === strcmp($current, 'lorem'))
                ->toArray()
        );
        $this->assertEquals(
            [RealmId::fromString('lorem'), RealmId::fromString('lorem'), RealmId::fromString('lorem')],
            Collection::fromArray([
                RealmId::fromString('lorem'),
                RealmId::fromString('ipsum'),
                RealmId::fromString('dolor'),
                RealmId::fromString('lorem'),
                RealmId::fromString('dolor'),
                RealmId::fromString('lorem'),
                RealmId::fromString('ipsum'),
                RealmId::fromString('dolor'),
            ])
                ->filter(fn (RealmId $current) => $current->equals('lorem'))
                ->toArray()
        );
    }

    /** @test */
    public function itRemovesDuplicates(): void
    {
        $this->assertEquals(
            ['lorem', 'ipsum', 'dolor'],
            Collection::fromArray(['lorem', 'ipsum', 'dolor', 'lorem', 'dolor', 'lorem', 'ipsum', 'dolor'])
                ->unique(fn (string $left, string $right) => 0 === strcmp($left, $right))
                ->toArray()
        );
        $this->assertEquals(
            [RealmId::fromString('lorem'), RealmId::fromString('ipsum'), RealmId::fromString('dolor')],
            Collection::fromArray([
                RealmId::fromString('lorem'),
                RealmId::fromString('ipsum'),
                RealmId::fromString('dolor'),
                RealmId::fromString('lorem'),
                RealmId::fromString('dolor'),
                RealmId::fromString('lorem'),
                RealmId::fromString('ipsum'),
                RealmId::fromString('dolor'),
            ])->unique(fn (RealmId $left, RealmId $right) => $left->equals($right))
                ->toArray()
        );
    }
}
