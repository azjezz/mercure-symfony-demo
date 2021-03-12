<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->set('EasyCorp\EasyLog\EasyLogHandler')
        ->private()
        ->args(['%kernel.logs_dir%/%kernel.environment%.log']);
};
