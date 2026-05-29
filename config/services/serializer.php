<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;
use Setono\SyliusPickupPointPlugin\Serializer\Normalizer\PickupPointNormalizer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointNormalizer::class)
        ->args([
            service(PickupPointIdentifierEncoder::class),
        ])
        ->tag('serializer.normalizer')
    ;
};
