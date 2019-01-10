<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ProviderPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        /** @var bool $hasService */
        $hasService = $container->has('setono.sylius_pickup_point.manager.provider_manager');

        if (!$hasService) {
            return;
        }

        $definition = $container->getDefinition('setono.sylius_pickup_point.manager.provider_manager');

        $taggedServices = $container->findTaggedServiceIds('setono.sylius_pickup_point.provider');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addProvider', [new Reference($id)]);
        }
    }
}
