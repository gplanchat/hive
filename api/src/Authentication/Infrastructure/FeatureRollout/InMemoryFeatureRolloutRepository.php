<?php

declare(strict_types=1);

namespace App\Authentication\Infrastructure\FeatureRollout;

use App\Authentication\Domain\FeatureRollout\FeatureRollout;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutRepositoryInterface;
use App\Authentication\Domain\FeatureRollout\Targets;
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
            \count($result),
            ...\array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public static function buildTestRepository(): self
    {
        return new self(
            new FeatureRollout(
                FeatureRolloutId::fromString('role.principal-administrator'),
                [Targets::Organization],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('subscription.enterprise'),
                [Targets::Organization],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.lorem-ipsum'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.dolor-sit-amet'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.consectetur-adipiscing-elit'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.fusce-vitae'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.ullamcorper-justo'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.nulla-urna-metus'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.blandit-et-felis-in'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.rhoncus-congue-metus'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.in-ipsum-magna'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.blandit-facilisis'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.vehicula-et'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.interdum-volutpat-lacus'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.vestibulum-sit-amet'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.varius-dolor'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.ut-porttitor-nulla'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.phasellus-pretium'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.lacinia-eros'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.nec-sagittis'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.maecenas-dignissim'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.sapien-et'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.sollicitudin-efficitur'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.libero-dui-consectetur'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.ligula-sit-amet'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.accumsan-nunc-massa'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.at-orci'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.praesent-ac'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.tristique-magna'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.in-semper-sapien'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.id-enim-placerat'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.fermentum-praesent'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.this-can-be-added'),
                [Targets::Organization, Targets::Global],
            ),
            new FeatureRollout(
                FeatureRolloutId::fromString('demo.this-can-also-be-added'),
                [Targets::Organization, Targets::Global],
            ),
        );
    }
}
