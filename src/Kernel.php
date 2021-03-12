<?php

declare(strict_types=1);

namespace App;

use Psl\Str;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Configures the container.
     *
     * You can register extensions:
     *
     *     $container->extension('framework', [
     *         'secret' => '%secret%'
     *     ]);
     *
     * Or services:
     *
     *     $container->services()->set('halloween', 'FooBundle\HalloweenProvider');
     *
     * Or parameters:
     *
     *     $container->parameters()->set('halloween', 'lot of fun');
     */
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/{packages}/*.php');
        $container->import(Str\format('../config/{packages}/%s/*.php', $this->environment));
        $container->import('../config/{services}.php');
        $container->import(Str\format('../config/{services}_%s.php', $this->environment));
    }

    /**
     * Adds or imports routes into your application.
     *
     *     $routes->import($this->getProjectDir().'/config/*.{yaml,php}');
     *     $routes
     *         ->add('admin_dashboard', '/admin')
     *         ->controller('App\Controller\AdminController::dashboard')
     *     ;
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(Str\format('../config/{routes}/%s/*.php', $this->environment));
        $routes->import('../config/{routes}/*.php');
        $routes->import('../config/{routes}.php');
    }
}
