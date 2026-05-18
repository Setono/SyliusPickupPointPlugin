<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Message\Handler\LoadPickupPointsHandler;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(LoadPickupPointsHandler::class)
        ->args([
            service('setono_sylius_pickup_point.registry.provider'),
            service('setono_sylius_pickup_point.repository.pickup_point'),
            service('doctrine'),
            param('setono_sylius_pickup_point.model.pickup_point.class'),
        ])
        ->tag('messenger.message_handler')
    ;
};
