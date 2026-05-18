<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Registry\ServiceRegistry;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('setono_sylius_pickup_point.registry.provider', ServiceRegistry::class)
        ->args([
            ProviderInterface::class,
            'pickup point provider',
        ])
    ;
};
