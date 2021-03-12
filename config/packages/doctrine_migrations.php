<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('doctrine_migrations', [
        'migrations_paths' => [
            'Migrations' => '%kernel.project_dir%/migrations',
        ],
    ]);

    $configurator->extension('doctrine_migrations', [
        'storage' => [
            'table_storage' => [
                'table_name' => 'migration_versions',
                'version_column_name' => 'version',
                'version_column_length' => 1024,
                'executed_at_column_name' => 'executed_at',
            ],
        ],
    ]);
};
