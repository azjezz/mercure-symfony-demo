<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $parameters = $configurator->parameters();
    $services = $configurator->services();
    $defaults = $services->defaults();

    $parameters
        ->set('mercure.secret', '!ChangeMe!');

    $defaults
        ->autowire()
        ->autoconfigure();

    $services
        ->load('App\\', __DIR__ . '/../src/*')
        ->exclude([__DIR__ . '/../src/{DependencyInjection,Entity,Tests,Kernel.php}']);

    $services
        ->load('App\Controller\\', __DIR__ . '/../src/Controller')
        ->tag('controller.service_arguments');

    $services
        ->alias(Symfony\Component\Security\Core\User\PasswordUpgraderInterface::class, App\Security\PasswordManager::class);

    $services
        ->set(App\Doctrine\EventListener\PostgresSchemaListener::class)
        ->tag('doctrine.event_listener', [
            'event' => 'postGenerateSchema'
        ]);
    
    $services
        ->set(App\Mercure\TokenProvider::class)
        ->arg('$secret', '%mercure.secret%');
};
