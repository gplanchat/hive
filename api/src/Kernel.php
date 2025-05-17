<?php

declare(strict_types=1);

namespace App;

use App\Platform\Infrastructure\Symfony\OpaqueObjectsExpressionLanguageProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDO\PgSQL\Driver;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->setDefinition('db.driver.pdo_pgsql', new Definition(Driver::class));

        // FIXME: make this dynamic
        $container->setDefinition('db.connection', new Definition(Connection::class, [
            [
                'user' => 'app',
                'password' => '!ChangeMe!',
                'host' => 'database',
                'port' => 5432,
                'dbname' => 'app',
            ],
            new Reference('db.driver.pdo_pgsql'),
        ]));

        $container->addExpressionLanguageProvider(new OpaqueObjectsExpressionLanguageProvider());
    }
}
