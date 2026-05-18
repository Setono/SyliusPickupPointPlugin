<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\EventListener\AddIndicesSubscriber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(AddIndicesSubscriber::class)
        ->tag('doctrine.event_subscriber')
    ;
};
