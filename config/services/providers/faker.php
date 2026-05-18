<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Provider\FakerProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('setono_sylius_pickup_point.provider.faker', FakerProvider::class)
        ->tag('setono_sylius_pickup_point.provider', [
            'code' => 'faker',
            'label' => 'setono_sylius_pickup_point.provider.faker',
        ])
    ;
};
