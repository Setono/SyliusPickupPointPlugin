<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Command\LoadPickupPointsCommand;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(LoadPickupPointsCommand::class)
        ->args([
            service('setono_sylius_pickup_point.registry.provider'),
            service('setono_sylius_pickup_point.command_bus'),
        ])
        ->tag('console.command')
    ;
};
