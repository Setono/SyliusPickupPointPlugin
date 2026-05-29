<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointByIdentifierAction;
use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointsSearchByCartAddressAction;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointsSearchByCartAddressAction::class)
        ->args([
            service('sylius.context.cart'),
            service(ProviderRegistry::class),
            service('serializer'),
        ])
        ->public()
    ;

    $services->set(PickupPointByIdentifierAction::class)
        ->args([
            service('serializer'),
            service(PickupPointIdentifierEncoder::class),
            service(ProviderRegistry::class),
        ])
        ->public()
    ;
};
