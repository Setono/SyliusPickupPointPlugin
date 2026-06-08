<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Fixture\Factory\ShippingMethodExampleFactory;
use Setono\SyliusPickupPointPlugin\Fixture\ShippingMethodFixture;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ShippingMethodExampleFactory::class)
        ->args([
            service('sylius.factory.shipping_method'),
            service('sylius.repository.zone'),
            service('sylius.repository.shipping_category'),
            service('sylius.repository.locale'),
            service('sylius.repository.channel'),
            service('sylius.repository.tax_category'),
        ])
    ;

    $services->set(ShippingMethodFixture::class)
        ->args([
            service('sylius.manager.shipping_method'),
            service(ShippingMethodExampleFactory::class),
        ])
        ->tag('sylius_fixtures.fixture')
    ;
};
