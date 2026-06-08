<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoder;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointTransformer;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShipmentTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointType;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointTransformer::class)
        ->args([
            service(PickupPointEncoder::class),
        ])
    ;

    $services->set(PickupPointType::class)
        ->args([
            service(PickupPointTransformer::class),
        ])
        ->tag('form.type')
    ;

    $services->set(ShipmentTypeExtension::class)
        ->tag('form.type_extension')
    ;

    $services->set(ShippingMethodChoiceTypeExtension::class)
        ->args([
            service(ProviderRegistry::class),
        ])
        ->tag('form.type_extension')
    ;

    $services->set(ShippingMethodTypeExtension::class)
        ->args([
            param('setono_sylius_pickup_point.providers'),
        ])
        ->tag('form.type_extension')
    ;
};
