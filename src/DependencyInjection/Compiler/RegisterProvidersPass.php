<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\Provider\CachedProvider;
use Setono\SyliusPickupPointPlugin\Provider\LocalProvider;
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
        $localEnabled = $container->getParameter('setono_sylius_pickup_point.local') === true;

        $typeToLabelMap = [];
        foreach ($container->findTaggedServiceIds('setono_sylius_pickup_point.provider') as $id => $tagged) {
            foreach ($tagged as $attributes) {
                if (!isset($attributes['code'], $attributes['label'])) {
                    throw new InvalidArgumentException('Tagged pickup point provider `' . $id . '` needs to have `code`, and `label` attributes.');
                }

                $typeToLabelMap[$attributes['code']] = $attributes['label'];

                if ($cacheEnabled) {
                    $decoratedId = $id;
                    $id .= '.cached'; // overwrite the id
                    $cachedDefinition = new Definition(CachedProvider::class, [
                        new Reference('setono_sylius_pickup_point.cache'),
                        new Reference($id . '.inner'),
                    ]);
                    $cachedDefinition->setDecoratedService($decoratedId, null, 256);

                    $container->setDefinition($id, $cachedDefinition);
                }

                if ($localEnabled) {
                    $decoratedId = $id;
                    $id .= '.local'; // overwrite the id
                    $cachedDefinition = new Definition(LocalProvider::class, [
                        new Reference($id . '.inner'),
                        new Reference('setono_sylius_pickup_point.repository.pickup_point'),
                    ]);
                    $cachedDefinition->setDecoratedService($decoratedId, null, 512);

                    $container->setDefinition($id, $cachedDefinition);
                }

                $registry->addMethodCall('register', [$attributes['code'], new Reference($id)]);
            }
        }

        $container->setParameter('setono_sylius_pickup_point.providers', $typeToLabelMap);
    }
}
