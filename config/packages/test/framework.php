<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('framework', [
        'test' => true,
    ]);

    $configurator->extension('framework', [
        'session' => [
            'storage_id' => 'session.storage.mock_file',
        ],
    ]);
};
