<?php

declare(strict_types=1);

use App\Mercure\TokenProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator->extension('mercure', [
        'enable_profiler' => '%kernel.debug%',
        'hubs' => [
            'default' => [
                'url' => '%env(MERCURE_PUBLISH_URL)%',
                'jwt_provider' => TokenProvider::class
            ]
        ]
    ]);
};
