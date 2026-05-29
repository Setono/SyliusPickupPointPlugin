<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Validator\Constraints\HasPickupPointSelectedValidator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(HasPickupPointSelectedValidator::class)
        ->tag('validator.constraint_validator')
    ;
};
