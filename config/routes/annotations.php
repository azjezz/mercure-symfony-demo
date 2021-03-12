<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $configurator): void {
    $configurator->import('../../src/Controller/', 'annotation');

    $configurator->import('../../src/Kernel.php', 'annotation');
};
