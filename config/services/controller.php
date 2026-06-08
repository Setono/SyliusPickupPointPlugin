<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointsAction;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoder;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointsAction::class)
        ->args([
            service('sylius.context.cart'),
            service(ProviderRegistry::class),
            service(PickupPointEncoder::class),
            service(ShippingMethodsResolverInterface::class),
        ])
        ->public()
    ;
};
