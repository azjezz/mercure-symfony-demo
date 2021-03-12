<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $configurator): void {
    $configurator->import('@WebProfilerBundle/Resources/config/routing/wdt.xml')
        ->prefix('/_wdt');

    $configurator->import('@WebProfilerBundle/Resources/config/routing/profiler.xml')
        ->prefix('/_profiler');
};
