<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointToIdentifierTransformer;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShipmentTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointChoiceType;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointIdChoiceType;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointToIdentifierTransformer::class)
        ->args([
            service(ProviderRegistry::class),
            service(PickupPointIdentifierEncoder::class),
        ])
    ;

    $services->set(PickupPointChoiceType::class)
        ->tag('form.type')
    ;

    $services->set(PickupPointIdChoiceType::class)
        ->tag('form.type')
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

    $services->set(ShipmentTypeExtension::class)
        ->tag('form.type_extension')
    ;
};
