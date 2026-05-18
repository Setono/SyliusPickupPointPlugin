<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Provider\DAOProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('setono_sylius_pickup_point.provider.dao', DAOProvider::class)
        ->args([
            service('setono_dao.client'),
        ])
        ->tag('setono_sylius_pickup_point.provider')
    ;
};
