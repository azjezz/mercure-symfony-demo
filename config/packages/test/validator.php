<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('framework', [
        'validation' => [
            'not_compromised_password' => false,
        ],
    ]);
};
