<?php

declare(strict_types=1);

namespace App\Cloud\Management\Infrastructure\CloudProviderAccount;

use App\Authentication\Domain\NotFoundException;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderTypes;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccount;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\CloudProviderAccountRepositoryInterface;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\OVHCloudCipheredCloudProviderCredentials;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases\CloudProviderAccountPage;
use App\Platform\Domain\Vault\Cipher\CipherInterface;

final class InMemoryCloudProviderAccountRepository implements CloudProviderAccountRepositoryInterface
{
    /**
     * @var CloudProviderAccount[]
     */
    private array $storage = [];

    public function __construct(
        CloudProviderAccount ...$cloudProviderAccounts,
    ) {
        $this->storage = $cloudProviderAccounts;
    }

    public function get(CloudProviderAccountId $cloudProviderAccountId): CloudProviderAccount
    {
        $result = array_filter($this->storage, fn (CloudProviderAccount $cloudProviderAccount) => $cloudProviderAccount->uuid->equals($cloudProviderAccountId));

        return array_shift($result) ?? throw new NotFoundException();
    }

    public function list(int $currentPage = 1, int $pageSize = 25): CloudProviderAccountPage
    {
        $result = $this->storage;

        return new CloudProviderAccountPage(
            $currentPage,
            $pageSize,
            \count($result),
            ...\array_slice($result, ($currentPage - 1) * $pageSize, $pageSize)
        );
    }

    public static function buildTestRepository(CipherInterface $cipher): self
    {
        return new self(
            new CloudProviderAccount(
                CloudProviderAccountId::fromString('0196db78-95ad-7017-8b1e-0b8d55f65f9e'),
                CloudProviderTypes::OVHCloud,
                'OVHCloud Gyroscops Europe',
                'OVHCloud Gyroscops Europe',
                new OVHCloudCipheredCloudProviderCredentials(
                    $cipher->encrypt('AAA'),
                    $cipher->encrypt('AAA'),
                    $cipher->encrypt('AAA'),
                    $cipher->encrypt('AAA'),
                ),
            ),
            new CloudProviderAccount(
                CloudProviderAccountId::fromString('0196db78-d3bb-7de1-ab8e-e03b202e9272'),
                CloudProviderTypes::OVHCloud,
                'OVHCloud Gyroscops North America',
                'OVHCloud Gyroscops North America',

                new OVHCloudCipheredCloudProviderCredentials(
                    $cipher->encrypt('AAA'),
                    $cipher->encrypt('AAA'),
                    $cipher->encrypt('AAA'),
                    $cipher->encrypt('AAA'),
                ),
            ),
        );
    }
}
