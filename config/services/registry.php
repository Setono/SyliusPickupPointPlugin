<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ProviderRegistry::class);

    $services->alias(ProviderRegistryInterface::class, ProviderRegistry::class);
};
