<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Provider\BudbeeProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('setono_sylius_pickup_point.provider.budbee', BudbeeProvider::class)
        ->args([
            service('setono_budbee.client.default'),
            service('setono_sylius_pickup_point.factory.pickup_point'),
            'bpost',
        ])
        ->tag('setono_sylius_pickup_point.provider', [
            'code' => 'budbee',
            'label' => 'setono_sylius_pickup_point.provider.budbee',
        ])
    ;
};
