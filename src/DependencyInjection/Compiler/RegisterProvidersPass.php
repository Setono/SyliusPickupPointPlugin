<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\Provider\CachedProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterProvidersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('setono_sylius_pickup_point.registry.provider')) {
            return;
        }

        $registry = $container->getDefinition('setono_sylius_pickup_point.registry.provider');
        $cacheEnabled = $container->getParameter('setono_sylius_pickup_point.cache.enabled') === true;

        $typeToLabelMap = [];
        foreach ($container->findTaggedServiceIds('setono_sylius_pickup_point.provider') as $id => $tagged) {
            foreach ($tagged as $attributes) {
                if (!isset($attributes['code'], $attributes['label'])) {
                    throw new InvalidArgumentException('Tagged pickup point provider `' . $id . '` needs to have `code`, and `label` attributes.');
                }

                $typeToLabelMap[$attributes['code']] = $attributes['label'];

                if ($cacheEnabled) {
                    $cachedDefinition = new Definition(CachedProvider::class);
                    $cachedDefinition->setDecoratedService($id);
                    $cachedDefinition->setPrivate(true);
                    $cachedDefinition->addArgument(new Reference('setono_sylius_pickup_point.cache'));
                    $cachedDefinition->addArgument(new Reference($id));

                    $registry->addMethodCall('register', [$attributes['code'], $cachedDefinition]);
                } else {
                    $registry->addMethodCall('register', [$attributes['code'], new Reference($id)]);
                }
            }
        }

        $container->setParameter('setono_sylius_pickup_point.providers', $typeToLabelMap);
    }
}
