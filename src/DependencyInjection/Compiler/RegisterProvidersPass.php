<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler;

use InvalidArgumentException;
use ReflectionClass;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class RegisterProvidersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('setono_sylius_pickup_point.registry.provider')) {
            return;
        }

        $registry = $container->getDefinition('setono_sylius_pickup_point.registry.provider');

        $codeToNameMap = [];
        foreach ($container->findTaggedServiceIds('setono_sylius_pickup_point.provider') as $id => $tagged) {
            foreach ($tagged as $attributes) {
                [$code, $name] = $this->resolveCodeAndName($container, $id, is_array($attributes) ? $attributes : []);

                $codeToNameMap[$code] = $name;
                $registry->addMethodCall('register', [$code, new Reference($id)]);
            }
        }

        $container->setParameter('setono_sylius_pickup_point.providers', $codeToNameMap);
    }

    /**
     * @param array<int|string, mixed> $tagAttributes
     *
     * @return array{0: string, 1: string}
     */
    private function resolveCodeAndName(ContainerBuilder $container, string $id, array $tagAttributes): array
    {
        $code = $tagAttributes['code'] ?? null;
        $name = $tagAttributes['name'] ?? null;

        if (is_string($code) && '' !== $code && is_string($name) && '' !== $name) {
            return [$code, $name];
        }

        $class = $container->getDefinition($id)->getClass();
        if (is_string($class) && class_exists($class)) {
            $reflection = new ReflectionClass($class);
            $attributes = $reflection->getAttributes(AsProvider::class);
            if ([] !== $attributes) {
                $attribute = $attributes[0]->newInstance();

                return [$attribute->code, $attribute->name];
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Tagged pickup point provider "%s" must either set `code` and `name` tag attributes or declare the #[%s] attribute on its class.',
            $id,
            AsProvider::class,
        ));
    }
}
