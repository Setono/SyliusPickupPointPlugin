<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoder;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoderInterface;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoderInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PickupPointIdentifierEncoder::class);

    $services->alias(PickupPointIdentifierEncoderInterface::class, PickupPointIdentifierEncoder::class);

    $services->set(PickupPointEncoder::class);

    $services->alias(PickupPointEncoderInterface::class, PickupPointEncoder::class);
};
