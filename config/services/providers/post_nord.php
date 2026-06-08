<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Provider\PostNordProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('setono_sylius_pickup_point.provider.post_nord', PostNordProvider::class)
        ->args([
            service('setono_post_nord.client'),
        ])
        ->tag('setono_sylius_pickup_point.provider')
    ;
};
