<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointByIdAction;
use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointsSearchByCartAddressAction;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointToIdentifierTransformer;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointsSearchByCartAddressAction::class)
        ->args([
            service('sylius.context.cart'),
            service(ProviderRegistry::class),
        ])
        ->public()
    ;

    $services->set(PickupPointByIdAction::class)
        ->args([
            service('serializer'),
            service(PickupPointToIdentifierTransformer::class),
        ])
        ->public()
    ;
};
