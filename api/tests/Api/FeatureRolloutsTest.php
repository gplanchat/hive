<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Authentication\Infrastructure\Organization\DataFixtures\OrganizationFixtures;
use Psr\Clock\ClockInterface;

class FeatureRolloutsTest extends ApiTestCase
{
    static ?bool $alwaysBootKernel = false;

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    /** @test */
    public function itShouldListFeatureRollouts(): void
    {
        static::createClient()->request('GET', '/authentication/feature-rollouts');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/FeatureRollout',
            '@id' => '/authentication/feature-rollouts',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 36,
        ]);
    }

    /** @test */
    public function itShouldShowAFeatureRollout(): void
    {
        static::createClient()->request('GET', '/authentication/feature-rollouts/subscription.enterprise');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/FeatureRollout',
            '@type' => 'FeatureRollout',
            '@id' => '/authentication/feature-rollouts/subscription.enterprise',
            'code' => 'subscription.enterprise',
        ]);
    }
}
