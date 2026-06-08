<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Shipping\OrderShippingMethodSelectionRequirementChecker;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(OrderShippingMethodSelectionRequirementChecker::class)
        ->decorate('sylius.checker.order_shipping_method_selection_requirement', null, 256)
        ->args([
            service('.inner'),
            service(ShippingMethodsResolverInterface::class),
        ])
    ;
};
