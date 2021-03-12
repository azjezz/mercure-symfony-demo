<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    // Encoders
    $configurator->extension('security', [
        'encoders' => [
            App\Security\UserInterface::class => [
                'algorithm' => 'argon2id'
            ],
        ],
    ]);

    // User providers
    $configurator->extension('security', [
        'providers' => [
            App\Security\UserProvider::class => [
                'id' => App\Security\UserProvider::class,
            ],
        ],
    ]);

    // Firewalls
    $configurator->extension('security', [
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'main' => [
                'anonymous' => true,
                'lazy' => true,
                'provider' => App\Security\UserProvider::class,
                'guard' => [
                    'authenticators' => [
                        App\Security\Authenticator::class
                    ],
                ],
                'logout' => [
                    'path' => 'user_logout',
                ],
            ],
        ],
    ]);

    // Role Hierarchy
    $configurator->extension('security', [
        'role_hierarchy' => App\Security\UserInterface::ROLE_HIERARCHY,
    ]);

    // Access Control
    $configurator->extension('security', [
        'access_control' => [
            ['path' => '^/admin', 'roles' => [App\Security\UserInterface::ROLE_MODERATOR]],
            ['path' => '^/user/(register|login)', 'allow_if' => 'is_anonymous()'],
            ['path' => '^/password-reset', 'allow_if' => 'is_anonymous()'],
        ],
    ]);
};
