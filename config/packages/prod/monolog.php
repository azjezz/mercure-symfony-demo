<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('monolog', [
        'handlers' => [
            'main' => [
                'type' => 'fingers_crossed',
                'action_level' => 'error',
                'handler' => 'nested',
                'excluded_http_codes' => [404, 405],
                'buffer_size' => 50,
            ],
            'nested' => [
                'type' => 'stream',
                'path' => '%kernel.logs_dir%/%kernel.environment%.log',
                'level' => 'debug',
            ],
            'console' => [
                'type' => 'console',
                'process_psr_3_messages' => false,
                'channels' => ['!event', '!doctrine'],
            ],
            'deprecation' => [
                'type' => 'stream',
                'path' => '%kernel.logs_dir%/%kernel.environment%.deprecations.log',
            ],
            'deprecation_filter' => [
                'type' => 'filter',
                'handler' => 'deprecation',
                'max_level' => 'info',
                'channels' => ['php'],
            ],
        ],
    ]);
};
