<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('framework', [
        'secret' => '%env(APP_SECRET)%',
    ]);

    $configurator->extension('framework', [
        'session' => [
            'handler_id' => null,
            'cookie_secure' => 'auto',
            'cookie_samesite' => 'lax',
        ],
    ]);

    $configurator->extension('framework', [
        'php_errors' => [
            'log' => true,
        ],
    ]);
};
