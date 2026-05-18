<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointToIdentifierTransformer;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShipmentTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointChoiceType;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointIdChoiceType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodType;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointToIdentifierTransformer::class)
        ->args([
            service('setono_sylius_pickup_point.registry.provider'),
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
            service('setono_sylius_pickup_point.registry.provider'),
            service('sylius.context.cart.composite'),
            service('security.csrf.token_manager'),
        ])
        ->tag('form.type_extension', ['extended_type' => ShippingMethodChoiceType::class])
    ;

    $services->set(ShippingMethodTypeExtension::class)
        ->args([
            param('setono_sylius_pickup_point.providers'),
        ])
        ->tag('form.type_extension', ['extended_type' => ShippingMethodType::class])
    ;

    $services->set(ShipmentTypeExtension::class)
        ->tag('form.type_extension', ['extended_type' => ShipmentType::class])
    ;
};
