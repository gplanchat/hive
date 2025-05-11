<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\FeatureRollout;

use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutRepositoryInterface;
use App\Authentication\Domain\FeatureRollout\UseCases\FeatureRolloutPage;
use App\Authentication\Domain\NotFoundException;

final class InMemoryFeatureRolloutRepository implements FeatureRolloutRepositoryInterface
{
    private array $storage = [];

    public function __construct(
        FeatureRollout ...$featureRollouts,
    ) {
        $this->storage = $featureRollouts;
    }

    public function get(FeatureRolloutId $featureRolloutId): FeatureRollout
    {
        $result = array_filter($this->storage, fn (FeatureRollout $featureRollout) => $featureRollout->code->equals($featureRolloutId));

        return array_shift($result) ?? throw new NotFoundException();
    }

    public function list(int $currentPage = 1, int $pageSize = 25): FeatureRolloutPage
    {
        $result = $this->storage;

        return new FeatureRolloutPage(
            $currentPage,
            $pageSize,
            count($result),
            ...array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public static function buildTestRepository(): self
    {
        return new self(
            new FeatureRollout(
                FeatureRolloutId::fromString('role.principal-administrator'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('subscription.enterprise'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.lorem-ipsum'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.dolor-sit-amet'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.consectetur-adipiscing-elit'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.fusce-vitae'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.ullamcorper-justo'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.nulla-urna-metus'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.blandit-et-felis-in'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.in-ipsum-magna'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.blandit-facilisis'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.vehicula-et'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.interdum-volutpat-lacus'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.vestibulum-sit-amet'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.varius-dolor'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.ut-porttitor-nulla'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.phasellus-pretium'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.lacinia-eros'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.nec-sagittis'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.maecenas-dignissim'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.sapien-et'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.sollicitudin-efficitur'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.libero-dui-consectetur'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.ligula-sit-amet'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.accumsan-nunc-massa'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.at-orci'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.praesent-ac'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.tristique-magna'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.in-semper-sapien'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.id-enim-placerat'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.fermentum-praesent'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.this-can-be-added'),
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.this-can-also-be-added'),
            ),
        );
    }
}
