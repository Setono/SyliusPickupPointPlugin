<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Provider\GlsProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('setono_sylius_pickup_point.provider.gls', GlsProvider::class)
        ->args([
            service('setono_gls_webservice.client'),
            service('setono_sylius_pickup_point.factory.pickup_point'),
        ])
        ->tag('setono_sylius_pickup_point.provider', [
            'code' => 'gls',
            'label' => 'setono_sylius_pickup_point.provider.gls',
        ])
    ;
};
