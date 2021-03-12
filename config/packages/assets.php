<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('framework', [
        'assets' => [
            'json_manifest_path' => '%kernel.project_dir%/public/build/manifest.json',
        ],
    ]);
};
