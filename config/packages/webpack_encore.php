<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('webpack_encore', [
        'output_path' => '%kernel.project_dir%/public/build',
    ]);
};
