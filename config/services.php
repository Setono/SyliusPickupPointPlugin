<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import('services/controller.php');
    $containerConfigurator->import('services/encoder.php');
    $containerConfigurator->import('services/fixture.php');
    $containerConfigurator->import('services/form.php');
    $containerConfigurator->import('services/registry.php');
    $containerConfigurator->import('services/shipping.php');
    $containerConfigurator->import('services/validator.php');
};
