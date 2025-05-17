<?php

declare(strict_types=1);

namespace App\Cloud\Management\Domain\CloudProviderAccount\Query;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Authentication\Domain\FeatureRollout\FeatureRolloutId;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderAccountId;
use App\Cloud\Management\Domain\CloudProviderAccount\CloudProviderTypes;
use App\Cloud\Management\Domain\CloudProviderAccount\Query\UseCases\QueryOneCloudProviderAccount;
use App\Cloud\Management\UserInterface\CloudProviderAccount\CloudProviderAccountOutput;
use App\Cloud\Management\UserInterface\CloudProviderAccount\QueryOneCloudProviderAccountProvider;
use App\Cloud\Management\UserInterface\CloudProviderAccount\QuerySeveralCloudProviderAccountProvider;

#[Get(
    uriTemplate: '/cloud/cloud-provider-accounts/{uuid}',
    uriVariables: [
        'uuid',
    ],
    openapi: new Operation(
        summary: 'Get Cloud Provider Account',
        parameters: [
            new Parameter(
                name: 'code',
                in: 'path',
                description: 'Code of the Cloud Provider Account',
                required: true,
                schema: ['type' => 'string', 'pattern' => CloudProviderAccountId::REQUIREMENT],
            ),
        ],
    ),
    security: 'is_granted("IS_AUTHENTICATED")',
    input: QueryOneCloudProviderAccount::class,
    //    output: CloudProviderAccountOutput::class,
    validate: true,
    provider: QueryOneCloudProviderAccountProvider::class,
)]
#[GetCollection(
    uriTemplate: '/cloud/cloud-provider-accounts',
    paginationEnabled: true,
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100,
    paginationPartial: true,
    order: ['code' => 'ASC'],
    security: 'is_granted("IS_AUTHENTICATED")',
    validate: true,
    provider: QuerySeveralCloudProviderAccountProvider::class,
    parameters: [
        'page' => new QueryParameter(schema: ['type' => 'integer', 'min' => 1]),
        'itemsPerPage' => new QueryParameter(schema: ['type' => 'integer', 'min' => 10, 'max' => 100]),
    ],
)]
final readonly class CloudProviderAccount
{
    /**
     * @param non-empty-string   $name
     * @param FeatureRolloutId[] $featureRolloutIds
     */
    public function __construct(
        public CloudProviderAccountId $uuid,
        public CloudProviderTypes $type,
        public string $name,
        public string $description,
        public CipheredCloudProviderCredentialsInterface $credentials,
        public array $featureRolloutIds = [],
    ) {
    }
}
