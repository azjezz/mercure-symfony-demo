<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('framework', [
        'mailer' => [
            'dsn' => '%env(MAILER_DSN)%',
            'headers' => [
                'from' => 'example <no-reply@example.com>',
            ],
        ],
    ]);
};
