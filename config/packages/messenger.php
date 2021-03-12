<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('framework', [
        'messenger' => [
            'failure_transport' => 'failed',
            'transports' => [
                'async' => 'doctrine://default?queue_name=async&auto_setup=false',
                'failed' => 'doctrine://default?queue_name=failed&auto_setup=false',
            ],
            'routing' => [
                // built-in
                SendEmailMessage::class => 'async',
            ],
        ],
    ]);
};
