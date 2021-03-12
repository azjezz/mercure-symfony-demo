<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('web_profiler', [
        'toolbar' => false,
    ]);

    $configurator->extension('web_profiler', [
        'intercept_redirects' => false,
    ]);

    $configurator->extension('framework', [
        'profiler' => [
            'collect' => false,
        ],
    ]);
};
