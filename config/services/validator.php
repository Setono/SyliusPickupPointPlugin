<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Validator\Constraints\HasPickupPointSelectedValidator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(HasPickupPointSelectedValidator::class)
        ->args([
            service('setono_sylius_pickup_point.registry.provider'),
        ])
        ->tag('validator.constraint_validator', ['alias' => 'setono_pickup_point_has_pickup_point_selected'])
    ;
};
