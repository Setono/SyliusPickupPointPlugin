<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointByIdAction;
use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointsSearchByCartAddressAction;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointToIdentifierTransformer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointsSearchByCartAddressAction::class)
        ->args([
            service('serializer'),
            service('sylius.context.cart.composite'),
            service('security.csrf.token_manager'),
            service('setono_sylius_pickup_point.registry.provider'),
        ])
        ->tag('controller.service_arguments')
    ;

    $services->set(PickupPointByIdAction::class)
        ->args([
            service('serializer'),
            service(PickupPointToIdentifierTransformer::class),
        ])
        ->tag('controller.service_arguments')
    ;
};
