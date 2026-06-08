<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\GLS\Webservice\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Provider\GlsProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('setono_sylius_pickup_point.provider.gls', GlsProvider::class)
        ->args([
            service(ClientInterface::class),
        ])
        ->tag('setono_sylius_pickup_point.provider')
    ;
};
